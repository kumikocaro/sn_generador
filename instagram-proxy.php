<?php
/**
 * Instagram Video Downloader Proxy
 * Evita problemas de CORS descargando desde el servidor
 */

// Permitir CORS desde tu dominio
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Manejar preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obtener URL de Instagram
$instagram_url = isset($_GET['url']) ? $_GET['url'] : '';

if (empty($instagram_url)) {
    echo json_encode(['error' => 'No se proporcionó URL']);
    exit;
}

// Validar que sea URL de Instagram
if (strpos($instagram_url, 'instagram.com') === false) {
    echo json_encode(['error' => 'URL no válida']);
    exit;
}

// Extraer shortcode
preg_match('/instagram\.com\/(?:p|reel|reels|tv)\/([A-Za-z0-9_-]+)/', $instagram_url, $matches);
if (empty($matches[1])) {
    echo json_encode(['error' => 'No se pudo extraer código del post']);
    exit;
}

$shortcode = $matches[1];

// Array para guardar errores de cada método
$errors = [];

// Método 0: Intentar con yt-dlp (la mejor opción si está instalado)
$ytdlp_path = trim(shell_exec('which yt-dlp 2>/dev/null') ?: shell_exec('which youtube-dl 2>/dev/null'));

if (!empty($ytdlp_path) && file_exists($ytdlp_path)) {
    $temp_dir = sys_get_temp_dir();
    $unique_id = uniqid();
    $output_template = "$temp_dir/instagram_$unique_id.mp4";
    
    // Ejecutar yt-dlp
    $command = escapeshellcmd($ytdlp_path) . ' -f "best[ext=mp4]" --no-warnings --no-check-certificate -o ' . 
               escapeshellarg($output_template) . ' ' . escapeshellarg($instagram_url) . ' 2>&1';
    
    exec($command, $output, $return_code);
    
    if ($return_code === 0 && file_exists($output_template)) {
        // Leer el archivo y codificarlo en base64
        $video_data = file_get_contents($output_template);
        $base64_video = base64_encode($video_data);
        
        // Eliminar archivo temporal
        unlink($output_template);
        
        echo json_encode([
            'success' => true,
            'video_data' => $base64_video,
            'method' => 'yt-dlp',
            'size' => strlen($video_data)
        ]);
        exit;
    } else {
        $errors[] = "yt-dlp: Failed. Output: " . implode(' ', $output);
    }
} else {
    $errors[] = "yt-dlp: Not installed on server";
}

// Método 1: Acceso directo a Instagram (funciona sin APIs externas)
// Instagram permite acceso público a través de su API embebida
$embed_url = "https://www.instagram.com/p/{$shortcode}/?__a=1&__d=dis";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $embed_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: en-US,en;q=0.5',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    $errors[] = "Instagram Direct curl error: $curl_error";
} else if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    
    // Buscar video en diferentes ubicaciones posibles
    $video_url = null;
    
    if (isset($data['items'][0]['video_versions'][0]['url'])) {
        $video_url = $data['items'][0]['video_versions'][0]['url'];
    } else if (isset($data['graphql']['shortcode_media']['video_url'])) {
        $video_url = $data['graphql']['shortcode_media']['video_url'];
    } else if (isset($data['items'][0]['media_type']) && $data['items'][0]['media_type'] == 2) {
        // Tipo 2 = video
        if (isset($data['items'][0]['video_url'])) {
            $video_url = $data['items'][0]['video_url'];
        }
    }
    
    if ($video_url) {
        echo json_encode([
            'success' => true,
            'video_url' => $video_url,
            'method' => 'instagram_direct'
        ]);
        exit;
    } else {
        $errors[] = "Instagram Direct: Video not found in response structure";
    }
} else {
    $errors[] = "Instagram Direct: HTTP $http_code";
}

// Método 2: Intentar con SKYMANSION API
$sky_url = 'https://api.skymansion.site/ig-dl/download/?url=' . urlencode($instagram_url);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sky_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    $errors[] = "SKYMANSION curl error: $curl_error";
} else if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    if (isset($data['video']) && !empty($data['video'])) {
        echo json_encode([
            'success' => true,
            'video_url' => $data['video'],
            'img' => isset($data['img']) ? $data['img'] : '',
            'method' => 'skymansion'
        ]);
        exit;
    } else {
        $errors[] = "SKYMANSION: No video in response. Response: " . substr($response, 0, 200);
    }
} else {
    $errors[] = "SKYMANSION: HTTP $http_code. Response: " . substr($response, 0, 200);
}

// Método 2: IGDownloader
$ig_url = 'https://v3.igdownloader.app/api/ajaxSearch';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ig_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'recaptchaToken' => '',
    'q' => $instagram_url,
    't' => 'media',
    'lang' => 'en'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    $errors[] = "IGDownloader curl error: $curl_error";
} else if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    if (isset($data['data'])) {
        // Buscar URL .mp4 en el HTML devuelto
        if (preg_match('/href="([^"]+\.mp4[^"]*)"/', $data['data'], $video_matches)) {
            echo json_encode([
                'success' => true,
                'video_url' => $video_matches[1],
                'method' => 'igdownloader'
            ]);
            exit;
        } else {
            $errors[] = "IGDownloader: No .mp4 found in HTML. Response: " . substr($data['data'], 0, 200);
        }
    } else {
        $errors[] = "IGDownloader: No data field. Response: " . substr($response, 0, 200);
    }
} else {
    $errors[] = "IGDownloader: HTTP $http_code. Response: " . substr($response, 0, 200);
}

// Método 3: SnapSave
$snap_url = 'https://snapsave.app/action.php?lang=en';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $snap_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'url=' . urlencode($instagram_url));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Origin: https://snapsave.app',
    'Referer: https://snapsave.app/'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    $errors[] = "SnapSave curl error: $curl_error";
} else if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    if (isset($data['data'])) {
        if (preg_match('/href="([^"]+\.mp4[^"]*)"/', $data['data'], $video_matches)) {
            echo json_encode([
                'success' => true,
                'video_url' => $video_matches[1],
                'method' => 'snapsave'
            ]);
            exit;
        } else {
            $errors[] = "SnapSave: No .mp4 found in HTML. Response: " . substr($data['data'], 0, 200);
        }
    } else {
        $errors[] = "SnapSave: No data field. Response: " . substr($response, 0, 200);
    }
} else {
    $errors[] = "SnapSave: HTTP $http_code. Response: " . substr($response, 0, 200);
}

// Si todo falló, devolver errores detallados
echo json_encode([
    'error' => 'No se pudo descargar el video desde ningún servicio',
    'shortcode' => $shortcode,
    'debug' => $errors,
    'tried_methods' => ['skymansion', 'igdownloader', 'snapsave']
]);
