<?php
/**
 * Plugin Name: Universal Web Scraper - Salta News
 * Description: Extrae contenido de cualquier sitio web para generar placas
 * Version: 1.0
 * Author: Salta News
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Registrar endpoint REST API
add_action('rest_api_init', function() {
    // CORS headers
    add_filter('rest_pre_serve_request', function($served, $result, $request, $server) {
        header('Access-Control-Allow-Origin: https://www.kumodev.com');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        return $served;
    }, 10, 4);
    
    // Manejar preflight OPTIONS
    add_filter('rest_pre_dispatch', function($result, $server, $request) {
        if ($request->get_method() === 'OPTIONS') {
            header('Access-Control-Allow-Origin: https://www.kumodev.com');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            return new WP_REST_Response(null, 200);
        }
        return $result;
    }, 10, 3);
    
    // Endpoint principal de scraping universal
    register_rest_route('universal/v1', '/extract', array(
        'methods' => 'GET',
        'callback' => 'universal_extract_content',
        'permission_callback' => '__return_true',
        'args' => array(
            'url' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'esc_url_raw',
                'validate_callback' => function($param) {
                    return filter_var($param, FILTER_VALIDATE_URL) !== false;
                }
            )
        )
    ));
});

function universal_extract_content($request) {
    $url = $request->get_param('url');
    
    if (empty($url)) {
        return new WP_Error('invalid_url', 'URL inválida o vacía', array('status' => 400));
    }
    
    // Intentar diferentes métodos de extracción
    $result = array(
        'titulo' => '',
        'contenido' => '',
        'imagenDataUri' => '',
        'url' => $url,
        'metodo' => '',
        'excerpt' => ''
    );
    
    // 1. Intentar WordPress REST API
    $wp_result = try_wordpress_api($url);
    if ($wp_result && !empty($wp_result['titulo'])) {
        $result = array_merge($result, $wp_result);
        $result['metodo'] = 'WordPress REST API';
        return $result;
    }
    
    // 2. Intentar scraping con meta tags OpenGraph
    $og_result = try_opengraph_scraping($url);
    if ($og_result && !empty($og_result['titulo'])) {
        $result = array_merge($result, $og_result);
        $result['metodo'] = 'OpenGraph Meta Tags';
        return $result;
    }
    
    // 3. Fallback: scraping básico del HTML
    $html_result = try_basic_html_scraping($url);
    if ($html_result && !empty($html_result['titulo'])) {
        $result = array_merge($result, $html_result);
        $result['metodo'] = 'HTML Scraping';
        return $result;
    }
    
    return new WP_Error('extraction_failed', 'No se pudo extraer contenido de esta URL. Verifica que el sitio sea accesible.', array('status' => 500));
}

// Intenta obtener datos desde WordPress REST API
function try_wordpress_api($url) {
    $parsed = parse_url($url);
    if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
        return null;
    }
    
    $base_url = $parsed['scheme'] . '://' . $parsed['host'];
    
    // Intentar obtener el post desde la API de WordPress
    $api_url = $base_url . '/wp-json/wp/v2/posts?per_page=1&_embed&slug=' . basename($parsed['path']);
    
    $response = wp_remote_get($api_url, array(
        'timeout' => 10,
        'headers' => array('User-Agent' => 'SaltaNews-UniversalScraper/1.0')
    ));
    
    if (is_wp_error($response)) {
        return null;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (empty($data) || !is_array($data) || !isset($data[0])) {
        return null;
    }
    
    $post = $data[0];
    
    $result = array(
        'titulo' => wp_strip_all_tags($post['title']['rendered'] ?? ''),
        'contenido' => wp_strip_all_tags($post['content']['rendered'] ?? ''),
        'excerpt' => wp_strip_all_tags($post['excerpt']['rendered'] ?? ''),
        'imagenes' => array() // NUEVO: Array de imágenes
    );
    
    // Obtener imagen destacada
    if (isset($post['_embedded']['wp:featuredmedia'][0]['source_url'])) {
        $image_url = $post['_embedded']['wp:featuredmedia'][0]['source_url'];
        $result['imagenes'][] = array(
            'url' => $image_url,
            'dataUri' => convert_image_to_data_uri($image_url),
            'tipo' => 'destacada'
        );
    }
    
    // Extraer TODAS las imágenes del contenido HTML
    $html_content = $post['content']['rendered'] ?? '';
    if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html_content, $matches)) {
        foreach ($matches[1] as $img_url) {
            // Convertir URLs relativas a absolutas
            if (strpos($img_url, 'http') !== 0) {
                $img_url = $base_url . $img_url;
            }
            
            // Evitar duplicados
            $already_added = false;
            foreach ($result['imagenes'] as $existing) {
                if ($existing['url'] === $img_url) {
                    $already_added = true;
                    break;
                }
            }
            
            if (!$already_added) {
                $result['imagenes'][] = array(
                    'url' => $img_url,
                    'dataUri' => convert_image_to_data_uri($img_url),
                    'tipo' => 'contenido'
                );
            }
        }
    }
    
    // Mantener compatibilidad con código anterior
    if (!empty($result['imagenes'])) {
        $result['imagenDataUri'] = $result['imagenes'][0]['dataUri'];
    } else {
        $result['imagenDataUri'] = '';
    }
    
    return $result;
}

// Intenta obtener datos desde meta tags OpenGraph
function try_opengraph_scraping($url) {
    $response = wp_remote_get($url, array(
        'timeout' => 10,
        'headers' => array('User-Agent' => 'SaltaNews-UniversalScraper/1.0')
    ));
    
    if (is_wp_error($response)) {
        return null;
    }
    
    $html = wp_remote_retrieve_body($response);
    
    if (empty($html)) {
        return null;
    }
    
    // Extraer meta tags OpenGraph
    $titulo = extract_og_tag($html, 'og:title') ?: extract_tag($html, 'title');
    $descripcion = extract_og_tag($html, 'og:description') ?: extract_meta_tag($html, 'description');
    $imagen_og = extract_og_tag($html, 'og:image');
    
    if (empty($titulo)) {
        return null;
    }
    
    $result = array(
        'titulo' => html_entity_decode(strip_tags($titulo)),
        'contenido' => html_entity_decode(strip_tags($descripcion)),
        'excerpt' => html_entity_decode(strip_tags(substr($descripcion, 0, 200))),
        'imagenes' => array()
    );
    
    $parsed = parse_url($url);
    $base_url = $parsed['scheme'] . '://' . $parsed['host'];
    
    // Agregar imagen OpenGraph primero
    if (!empty($imagen_og)) {
        if (strpos($imagen_og, 'http') !== 0) {
            $imagen_og = $base_url . $imagen_og;
        }
        $result['imagenes'][] = array(
            'url' => $imagen_og,
            'dataUri' => convert_image_to_data_uri($imagen_og),
            'tipo' => 'opengraph'
        );
    }
    
    // Extraer todas las imágenes del HTML
    if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
        foreach ($matches[1] as $img_url) {
            if (strpos($img_url, 'http') !== 0) {
                $img_url = $base_url . $img_url;
            }
            
            // Evitar duplicados
            $already_added = false;
            foreach ($result['imagenes'] as $existing) {
                if ($existing['url'] === $img_url) {
                    $already_added = true;
                    break;
                }
            }
            
            if (!$already_added && strlen($img_url) > 10) {
                $result['imagenes'][] = array(
                    'url' => $img_url,
                    'dataUri' => convert_image_to_data_uri($img_url),
                    'tipo' => 'contenido'
                );
            }
        }
    }
    
    // Mantener compatibilidad
    if (!empty($result['imagenes'])) {
        $result['imagenDataUri'] = $result['imagenes'][0]['dataUri'];
    } else {
        $result['imagenDataUri'] = '';
    }
    
    return $result;
}

// Intenta scraping básico del HTML
function try_basic_html_scraping($url) {
    $response = wp_remote_get($url, array(
        'timeout' => 10,
        'headers' => array('User-Agent' => 'SaltaNews-UniversalScraper/1.0')
    ));
    
    if (is_wp_error($response)) {
        return null;
    }
    
    $html = wp_remote_retrieve_body($response);
    
    if (empty($html)) {
        return null;
    }
    
    // Extraer título
    $titulo = extract_tag($html, 'title') ?: extract_tag($html, 'h1');
    
    // Extraer primer párrafo significativo
    $contenido = '';
    if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) {
        foreach ($matches[1] as $p) {
            $text = strip_tags($p);
            $text = html_entity_decode($text);
            $text = trim($text);
            if (strlen($text) > 100) { // Solo párrafos con más de 100 caracteres
                $contenido .= $text . "\n\n";
                if (strlen($contenido) > 500) break; // Limitar a ~500 caracteres
            }
        }
    }
    
    if (empty($titulo)) {
        return null;
    }
    
    return array(
        'titulo' => html_entity_decode(strip_tags($titulo)),
        'contenido' => trim($contenido),
        'excerpt' => substr(trim($contenido), 0, 200),
        'imagenDataUri' => ''
    );
}

// Extrae meta tag OpenGraph
function extract_og_tag($html, $property) {
    if (preg_match('/<meta[^>]+property=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\'](.*?)["\']/i', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

// Extrae meta tag estándar
function extract_meta_tag($html, $name) {
    if (preg_match('/<meta[^>]+name=["\']' . preg_quote($name, '/') . '["\'][^>]+content=["\'](.*?)["\']/i', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

// Extrae contenido de un tag HTML
function extract_tag($html, $tag) {
    if (preg_match('/<' . preg_quote($tag, '/') . '[^>]*>(.*?)<\/' . preg_quote($tag, '/') . '>/is', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

// Convierte imagen a Data URI
function convert_image_to_data_uri($image_url) {
    if (empty($image_url)) {
        return '';
    }
    
    $response = wp_remote_get($image_url, array(
        'timeout' => 10,
        'headers' => array('User-Agent' => 'SaltaNews-UniversalScraper/1.0')
    ));
    
    if (is_wp_error($response)) {
        return '';
    }
    
    $image_data = wp_remote_retrieve_body($response);
    
    if (empty($image_data)) {
        return '';
    }
    
    // Detectar tipo de imagen
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->buffer($image_data);
    
    return 'data:' . $mime_type . ';base64,' . base64_encode($image_data);
}
