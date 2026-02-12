<?php
/**
 * Plugin Name: Salta News - Auto Placas Instagram
 * Description: Genera automáticamente placas para Instagram cuando se publica un post
 * Version: 1.0
 * Author: Salta News
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Enviar el mail solo cuando el post REALMENTE pasa a "publicado" (ahora o cuando se cumple la programación).
// Así no se envía al programar (status future) y sí cuando se publica (inmediato o por cron).
add_action('transition_post_status', 'sn_auto_placa_on_publish', 10, 3);

function sn_auto_placa_on_publish($new_status, $old_status, $post) {
    // Solo cuando pasa a "publicado" y no era ya publicado (evita reenvío al editar)
    if ($new_status !== 'publish' || $old_status === 'publish') {
        return;
    }
    // Solo posts y páginas
    if (!in_array($post->post_type, array('post', 'page'), true)) {
        return;
    }
    sn_auto_generate_placa($post->ID, $post);
}

function sn_auto_generate_placa($post_id, $post) {
    // Log para debug (solo si WP_DEBUG está activado)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("SN Auto Placas: Hook ejecutado para post ID: $post_id");
    }
    
    // Solo procesar posts publicados
    if ($post->post_status !== 'publish') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("SN Auto Placas: Post no está publicado. Status: " . $post->post_status);
        }
        return;
    }
    
    // Evitar ejecuciones duplicadas
    $already_sent = get_post_meta($post_id, '_sn_placa_sent', true);
    if ($already_sent === 'yes') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("SN Auto Placas: Email ya fue enviado para post ID: $post_id");
        }
        return;
    }
    
    $post_url = get_permalink($post_id);
    
    // Email de destino: puedes configurar un email específico aquí
    // Opción 1: Usar el email del administrador de WordPress (por defecto)
    $admin_email = get_option('admin_email');
    
    // Verificar que el email esté configurado
    if (empty($admin_email)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("SN Auto Placas: ERROR - No hay email de administrador configurado");
        }
        return;
    }
    
    // Opción 2: Usar un email específico (descomenta y cambia el email)
    // $admin_email = 'tu-email@ejemplo.com';
    
    // Opción 3: Usar múltiples emails (descomenta y agrega los emails que quieras)
    // $admin_email = array('email1@ejemplo.com', 'email2@ejemplo.com');
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("SN Auto Placas: Enviando email a: $admin_email");
    }
    
    // Generar token único para seguridad
    $token = wp_generate_password(32, false);
    
    // Guardar token en meta del post (temporal, 24 horas)
    update_post_meta($post_id, '_sn_share_token', $token);
    update_post_meta($post_id, '_sn_share_expires', time() + (24 * 60 * 60));
    update_post_meta($post_id, '_sn_placa_sent', 'yes');
    
    // URL de la página de publicación rápida
    // IMPORTANTE: Ajusta esta URL según donde tengas tu archivo HTML
    // Si está en otro dominio (ej: kumodev.com), usa la URL completa:
    $share_url = 'https://www.kumodev.com/sn/gensn.html?auto=' . $post_id . '&token=' . $token;
    // Si está en el mismo dominio que WordPress, usa:
    // $share_url = home_url('/sn/gensn.html?auto=' . $post_id . '&token=' . $token);
    
    // Preparar email
    $subject = '📱 Nueva placa lista para Instagram - ' . get_the_title($post_id);
    
    $message = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { background-color: #28a745; color: white; padding: 15px 30px; 
                     text-decoration: none; border-radius: 5px; display: inline-block; 
                     margin: 20px 0; font-weight: bold; }
            .info { background-color: #f0f2f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .footer { font-size: 12px; color: #666; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>📱 Nueva entrada lista para compartir en Instagram</h2>
            
            <div class="info">
                <p><strong>📰 Título:</strong> ' . esc_html(get_the_title($post_id)) . '</p>
                <p><strong>🔗 URL del post:</strong> <a href="' . esc_url($post_url) . '">' . esc_html($post_url) . '</a></p>
            </div>
            
            <p>La placa y el texto para Instagram ya están preparados. Solo necesitas hacer clic en el botón:</p>
            
            <a href="' . esc_url($share_url) . '" class="button">
                📱 Abrir y Compartir en Instagram
            </a>
            
            <p><strong>¿Qué pasará?</strong></p>
            <ul>
                <li>Se abrirá la página con la placa ya generada</li>
                <li>El texto por IA ya estará copiado en tu portapapeles</li>
                <li>Solo toca "Compartir" y selecciona Instagram</li>
            </ul>
            
            <div class="footer">
                <p><small>⏰ Este enlace expira en 24 horas por seguridad</small></p>
                <p><small>🔒 Token único: ' . substr($token, 0, 8) . '...</small></p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Intentar enviar el email
    $mail_sent = wp_mail($admin_email, $subject, $message, $headers);
    
    if ($mail_sent) {
        // Marcar como enviado solo si el email se envió correctamente
        update_post_meta($post_id, '_sn_placa_sent', 'yes');
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("SN Auto Placas: Email enviado exitosamente a: $admin_email");
        }
    } else {
        // Si falla, no marcar como enviado para poder reintentar
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("SN Auto Placas: ERROR - No se pudo enviar el email a: $admin_email");
        }
        
        // Intentar enviar un email de error al admin (si es diferente)
        $admin_email_error = get_option('admin_email');
        if ($admin_email_error && $admin_email_error !== $admin_email) {
            wp_mail($admin_email_error, 
                'Error: No se pudo enviar email de placa', 
                "No se pudo enviar el email de placa para el post ID: $post_id\n\nEmail destino: $admin_email",
                array('Content-Type: text/plain; charset=UTF-8')
            );
        }
    }
}

// Agregar headers CORS y registrar endpoints REST API
add_action('rest_api_init', function() {
    // Verificar que las funciones callback existan
    if (!function_exists('sn_get_auto_post_data')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('SN Auto Placas: ERROR - Función sn_get_auto_post_data no existe');
        }
        return;
    }
    
    // Permitir CORS desde kumodev.com
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
    
    // Endpoint REST API para validar token y obtener datos del post
    $route_result = register_rest_route('placas/v1', '/auto-post/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'sn_get_auto_post_data',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
            'token' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field'
            )
        )
    ));
    
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('SN Auto Placas: Endpoint /auto-post/ registrado: ' . ($route_result ? 'OK' : 'ERROR'));
    }
    
    // Endpoint de prueba para verificar que funciona
    register_rest_route('placas/v1', '/test-endpoint', array(
        'methods' => 'GET',
        'callback' => function() {
            return array('status' => 'ok', 'message' => 'Endpoint funcionando correctamente');
        },
        'permission_callback' => '__return_true'
    ));
    
    // Endpoint de prueba para diagnosticar problemas de email
    register_rest_route('placas/v1', '/test', array(
        'methods' => 'GET',
        'callback' => 'sn_test_email',
        'permission_callback' => '__return_true'
    ));
    
    // Endpoint de diagnóstico para verificar que los endpoints están registrados
    register_rest_route('placas/v1', '/debug', array(
        'methods' => 'GET',
        'callback' => function() {
            global $wp_rest_server;
            $routes = $wp_rest_server->get_routes();
            $placas_routes = array();
            foreach ($routes as $route => $handlers) {
                if (strpos($route, '/placas/v1/') !== false) {
                    $placas_routes[$route] = array_keys($handlers);
                }
            }
            return array(
                'plugin_activo' => true,
                'funciones_disponibles' => array(
                    'sn_get_auto_post_data' => function_exists('sn_get_auto_post_data'),
                    'sn_test_email' => function_exists('sn_test_email')
                ),
                'endpoints_registrados' => $placas_routes
            );
        },
        'permission_callback' => '__return_true'
    ));
    
    // Endpoint de prueba SIN token (solo para debugging - ELIMINAR EN PRODUCCIÓN)
    register_rest_route('placas/v1', '/test-post/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => function($request) {
            $post_id = intval($request['id']);
            $post = get_post($post_id);
            
            if (!$post) {
                return new WP_Error('post_not_found', 'Post no encontrado', array('status' => 404));
            }
            
            if ($post->post_status !== 'publish') {
                return new WP_Error('post_not_published', 'Post no está publicado. Status: ' . $post->post_status, array('status' => 403));
            }
            
            // Obtener imagen destacada
            $image_url = get_the_post_thumbnail_url($post_id, 'full');
            $image_data_uri = '';
            
            if ($image_url) {
                $image_data = @file_get_contents($image_url);
                if ($image_data !== false) {
                    $image_data_uri = 'data:image/jpeg;base64,' . base64_encode($image_data);
                }
            }
            
            // Obtener contenido limpio
            $content = wp_strip_all_tags($post->post_content);
            $content = wp_trim_words($content, 200, '...');
            
            // Verificar token almacenado
            $stored_token = get_post_meta($post_id, '_sn_share_token', true);
            $expires = get_post_meta($post_id, '_sn_share_expires', true);
            
            return array(
                'titulo' => get_the_title($post_id),
                'contenido' => $content,
                'url' => get_permalink($post_id),
                'imagenDataUri' => $image_data_uri ? substr($image_data_uri, 0, 50) . '...' : '',
                'excerpt' => get_the_excerpt($post_id),
                'debug_info' => array(
                    'post_id' => $post_id,
                    'post_status' => $post->post_status,
                    'has_token' => !empty($stored_token),
                    'token_expires' => $expires ? date('Y-m-d H:i:s', $expires) : 'N/A',
                    'token_valid' => !empty($expires) && time() < intval($expires),
                    'has_image' => !empty($image_url)
                )
            );
        },
        'permission_callback' => '__return_true'
    ));
}, 10);

function sn_get_auto_post_data($request) {
    $post_id = intval($request['id']);
    $token = sanitize_text_field($request->get_param('token'));
    
    // Validar que el token esté presente
    if (empty($token)) {
        return new WP_Error('missing_token', 'Token requerido. Agrega ?token=TU_TOKEN a la URL', array('status' => 400));
    }
    
    // Validar que el post exista primero
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('post_not_found', 'Post no encontrado con ID: ' . $post_id, array('status' => 404));
    }
    
    if ($post->post_status !== 'publish') {
        return new WP_Error('post_not_published', 'El post existe pero no está publicado. Status: ' . $post->post_status, array('status' => 403));
    }
    
    // Validar token
    $stored_token = get_post_meta($post_id, '_sn_share_token', true);
    $expires = get_post_meta($post_id, '_sn_share_expires', true);
    
    if (empty($stored_token)) {
        return new WP_Error('no_token_stored', 'Este post no tiene un token generado. Publica el post nuevamente para generar un nuevo token.', array('status' => 403));
    }
    
    if ($stored_token !== $token) {
        return new WP_Error('invalid_token', 'Token inválido. El token proporcionado no coincide con el almacenado para este post.', array('status' => 403));
    }
    
    if (empty($expires) || time() > intval($expires)) {
        $expired_date = $expires ? date('Y-m-d H:i:s', $expires) : 'N/A';
        return new WP_Error('expired_token', 'Token expirado. El token expiró el: ' . $expired_date . '. Publica el post nuevamente para generar un nuevo token.', array('status' => 403));
    }
    
    // Obtener imagen destacada
    $image_url = get_the_post_thumbnail_url($post_id, 'full');
    $image_data_uri = '';
    
    if ($image_url) {
        // Intentar obtener la imagen
        $image_data = @file_get_contents($image_url);
        if ($image_data !== false) {
            $image_data_uri = 'data:image/jpeg;base64,' . base64_encode($image_data);
        }
    }
    
    // Obtener contenido limpio
    $content = wp_strip_all_tags($post->post_content);
    $content = wp_trim_words($content, 200, '...');
    
    return array(
        'titulo' => get_the_title($post_id),
        'contenido' => $content,
        'url' => get_permalink($post_id),
        'imagenDataUri' => $image_data_uri,
        'excerpt' => get_the_excerpt($post_id)
    );
}

function sn_test_email($request) {
    $admin_email = get_option('admin_email');
    
    $test_result = array(
        'email_configurado' => !empty($admin_email),
        'email' => $admin_email,
        'wp_mail_disponible' => function_exists('wp_mail'),
        'plugin_activo' => is_plugin_active('sn-auto-placas/sn-auto-placas.php'),
    );
    
    // Intentar enviar un email de prueba
    if (!empty($admin_email) && function_exists('wp_mail')) {
        $test_subject = '🧪 Prueba - Auto Placas Instagram';
        $test_message = 'Este es un email de prueba del plugin Auto Placas Instagram. Si recibes esto, el sistema de email funciona correctamente.';
        $test_headers = array('Content-Type: text/html; charset=UTF-8');
        
        $mail_result = wp_mail($admin_email, $test_subject, $test_message, $test_headers);
        $test_result['email_prueba_enviado'] = $mail_result;
    }
    
    return $test_result;
}

// Limpiar tokens expirados (ejecutar diariamente)
add_action('wp_scheduled_delete', 'sn_cleanup_expired_tokens');

function sn_cleanup_expired_tokens() {
    global $wpdb;
    $expired_time = time();
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} 
         WHERE meta_key = '_sn_share_token' 
         AND post_id IN (
             SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = '_sn_share_expires' 
             AND meta_value < %d
         )",
        $expired_time
    ));
}
