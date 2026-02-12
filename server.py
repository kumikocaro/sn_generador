#!/usr/bin/env python3
"""
Servidor local para gensn-video
Descarga videos de Instagram usando yt-dlp
"""

from flask import Flask, request, jsonify, send_from_directory, send_file
from flask_cors import CORS
import subprocess
import json
import os
import tempfile
import base64
import re
from PIL import Image, ImageDraw, ImageFont
import io

app = Flask(__name__)
CORS(app)  # Permitir peticiones desde el frontend

# Agregar headers necesarios para SharedArrayBuffer (requerido por ffmpeg.wasm)
# Solo para páginas HTML, no para recursos estáticos
@app.after_request
def add_security_headers(response):
    # Solo agregar headers COOP/COEP para HTML
    if response.content_type and 'text/html' in response.content_type:
        response.headers['Cross-Origin-Embedder-Policy'] = 'require-corp'
        response.headers['Cross-Origin-Opener-Policy'] = 'same-origin'
    # Para otros recursos, agregar CORS permisivo
    else:
        response.headers['Cross-Origin-Resource-Policy'] = 'cross-origin'
    return response

@app.route('/')
def index():
    return send_from_directory('.', 'gensn-video.html')

@app.route('/<path:path>')
def serve_static(path):
    return send_from_directory('.', path)

@app.route('/api/download', methods=['GET', 'POST'])
def download_instagram():
    """Descarga video de Instagram usando yt-dlp"""
    
    # Obtener URL de Instagram
    instagram_url = request.args.get('url')
    
    # Si es POST, intentar obtener del body JSON
    if not instagram_url and request.is_json:
        instagram_url = request.json.get('url')
    
    # Si es POST form-data
    if not instagram_url and request.form:
        instagram_url = request.form.get('url')
    
    if not instagram_url:
        return jsonify({'error': 'No se proporcionó URL'}), 400
    
    if 'instagram.com' not in instagram_url:
        return jsonify({'error': 'URL no válida'}), 400
    
    # Extraer shortcode
    match = re.search(r'instagram\.com/(?:p|reel|reels|tv)/([A-Za-z0-9_-]+)', instagram_url)
    if not match:
        return jsonify({'error': 'No se pudo extraer código del post'}), 400
    
    shortcode = match.group(1)
    
    try:
        # Crear directorio temporal
        with tempfile.TemporaryDirectory() as temp_dir:
            output_file = os.path.join(temp_dir, f'instagram_{shortcode}.mp4')
            
            # Buscar yt-dlp en múltiples ubicaciones
            ytdlp_paths = [
                'yt-dlp',  # En PATH
                '/Users/macbookair/Library/Python/3.9/bin/yt-dlp',  # Instalación --user
                os.path.expanduser('~/Library/Python/3.9/bin/yt-dlp'),  # Expandido
                '/usr/local/bin/yt-dlp'  # Instalación global
            ]
            
            ytdlp_cmd = 'yt-dlp'
            for path in ytdlp_paths:
                if os.path.exists(path) or subprocess.run(['which', path], capture_output=True).returncode == 0:
                    ytdlp_cmd = path
                    break
            
            # Primero extraer metadatos del video
            print(f"🌐 Extrayendo metadatos: {instagram_url}")
            metadata_command = [
                ytdlp_cmd,
                '--dump-json',
                '--no-warnings',
                '--no-check-certificate',
                instagram_url
            ]
            
            metadata_result = subprocess.run(metadata_command, capture_output=True, text=True, timeout=30)
            
            metadata = {}
            if metadata_result.returncode == 0:
                try:
                    metadata = json.loads(metadata_result.stdout)
                    print(f"📝 Metadatos extraídos: {metadata.get('title', 'N/A')[:50]}...")
                except json.JSONDecodeError:
                    print("⚠️ No se pudieron parsear los metadatos")
            
            # Ejecutar yt-dlp para descargar el video
            command = [
                ytdlp_cmd,
                '-f', 'best[ext=mp4]',
                '--no-warnings',
                '--no-check-certificate',
                '-o', output_file,
                instagram_url
            ]
            
            print(f"🌐 Descargando video: {instagram_url}")
            result = subprocess.run(command, capture_output=True, text=True, timeout=60)
            
            if result.returncode != 0:
                error_msg = (result.stderr or result.stdout or '')[:500]
                print(f"❌ Error yt-dlp: {error_msg}")
                return jsonify({
                    'error': 'No se pudo descargar desde Instagram. En servidores en la nube Instagram suele bloquear. Probá subir el video manualmente abajo.',
                    'debug': error_msg
                }), 500
            
            # Verificar que el archivo existe
            if not os.path.exists(output_file):
                return jsonify({'error': 'Video no descargado'}), 500
            
            # Leer video y convertir a base64
            with open(output_file, 'rb') as f:
                video_data = f.read()
            
            video_base64 = base64.b64encode(video_data).decode('utf-8')
            
            print(f"✅ Video descargado: {len(video_data) / 1024 / 1024:.2f} MB")
            
            # Extraer descripción y título de los metadatos
            description = metadata.get('description', '') or ''
            title = metadata.get('title', '') or ''
            
            # Limpiar descripción (a veces viene con mucho texto extra)
            if description:
                # Instagram a veces pone el título al inicio de la descripción
                description = description.strip()
            
            return jsonify({
                'success': True,
                'video_data': video_base64,
                'method': 'yt-dlp-local',
                'size': len(video_data),
                'shortcode': shortcode,
                'description': description,
                'title': title,
                'uploader': metadata.get('uploader', ''),
                'timestamp': metadata.get('timestamp', '')
            })
    
    except subprocess.TimeoutExpired:
        return jsonify({'error': 'Timeout al descargar video'}), 504
    except Exception as e:
        print(f"❌ Error: {str(e)}")
        return jsonify({'error': str(e)}), 500

def find_ffmpeg():
    """Busca ffmpeg en varios lugares comunes"""
    import shutil
    # Buscar en PATH
    ffmpeg_path = shutil.which('ffmpeg')
    if ffmpeg_path:
        return ffmpeg_path
    
    # Buscar en ubicaciones comunes de macOS
    common_paths = [
        '/usr/local/bin/ffmpeg',
        '/opt/homebrew/bin/ffmpeg',
        '/usr/bin/ffmpeg',
        './ffmpeg',  # Versión portable en el directorio actual
    ]
    
    for path in common_paths:
        if os.path.exists(path) and os.access(path, os.X_OK):
            return path
    
    return None

def create_watermark_image(output_path):
    """Crea imagen PNG del logo SN EXACTO como en gensn-video.html"""
    # Crear imagen transparente (200x100 como en el frontend)
    img = Image.new('RGBA', (200, 100), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    
    # Buscar fuente Montserrat o similar bold/black italic
    font_size = 60  # Igual que en el frontend
    font = None
    
    # Buscar Montserrat primero
    montserrat_paths = [
        '/System/Library/Fonts/Supplemental/Montserrat-BlackItalic.ttf',
        '/System/Library/Fonts/Supplemental/Montserrat-BoldItalic.ttf',
        '/Library/Fonts/Montserrat-BlackItalic.ttf',
        '/Users/macbookair/Library/Fonts/Montserrat-BlackItalic.ttf',
    ]
    
    for font_path in montserrat_paths:
        try:
            if os.path.exists(font_path):
                font = ImageFont.truetype(font_path, font_size)
                break
        except:
            continue
    
    # Si no hay Montserrat, usar Arial Bold Italic como fallback
    if not font:
        arial_paths = [
            '/System/Library/Fonts/Supplemental/Arial Bold Italic.ttf',
            '/System/Library/Fonts/Supplemental/Arial.ttf',
        ]
        for font_path in arial_paths:
            try:
                if os.path.exists(font_path):
                    font = ImageFont.truetype(font_path, font_size)
                    break
            except:
                continue
    
    if not font:
        font = ImageFont.load_default()
    
    # Medir texto (igual que en el frontend)
    s_bbox = draw.textbbox((0, 0), 'S', font=font)
    wS = s_bbox[2] - s_bbox[0]
    
    n_bbox = draw.textbbox((0, 0), 'N', font=font)
    wN = n_bbox[2] - n_bbox[0]
    
    # Gap igual que frontend: 60 * 0.15 = 9
    gap = font_size * 0.15
    totW = wS - gap + wN
    
    # Centrar
    curX = (img.width - totW) / 2
    centerY = img.height / 2
    
    # Sombra (igual que frontend: rgba(0,0,0,0.8), blur 4, offset 2,2)
    # En PIL simulamos sombra con múltiples capas
    shadow_color = (0, 0, 0, 204)  # rgba(0,0,0,0.8) = 255*0.8 = 204
    shadow_offset = 2
    
    # Dibujar "S" con sombra primero
    s_x = curX + wS/2
    # Sombra (múltiples capas para simular blur)
    for i in range(3):
        offset = shadow_offset + i
        draw.text((s_x + offset, centerY + offset), 'S', fill=shadow_color, font=font, anchor='mm')
    # Texto principal rojo (#b42727)
    draw.text((s_x, centerY), 'S', fill=(180, 39, 39, 255), font=font, anchor='mm')
    
    # Mover posición para "N"
    curX += wS - gap
    
    # Dibujar "N" con sombra
    n_x = curX + wN/2
    # Sombra
    for i in range(3):
        offset = shadow_offset + i
        draw.text((n_x + offset, centerY + offset), 'N', fill=shadow_color, font=font, anchor='mm')
    # Texto principal azul (#2a2972)
    draw.text((n_x, centerY), 'N', fill=(42, 41, 114, 255), font=font, anchor='mm')
    
    # Guardar como PNG
    img.save(output_path, 'PNG')
    return output_path

@app.route('/api/process-video', methods=['POST'])
def process_video():
    """Procesa video: agrega portada usando ffmpeg"""
    
    try:
        # Recibir datos
        data = request.get_json()
        
        if not data:
            return jsonify({'error': 'No se recibieron datos'}), 400
        
        video_data = data.get('video_data')  # Base64
        cover_image_data = data.get('cover_image')  # Base64 de la portada
        watermark_image_data = data.get('watermark_image')  # Logo SN desde frontend (igual que la placa)
        
        if not video_data:
            return jsonify({'error': 'No se proporcionó video'}), 400
        
        # Buscar ffmpeg
        ffmpeg_cmd = find_ffmpeg()
        if not ffmpeg_cmd:
            # Si no hay ffmpeg, devolver video original
            return jsonify({
                'success': True,
                'video_data': video_data,
                'size': len(base64.b64decode(video_data)),
                'note': 'Video sin procesar (ffmpeg no encontrado)'
            })
        
        # Crear directorio temporal
        with tempfile.TemporaryDirectory() as temp_dir:
            # Guardar video
            video_path = os.path.join(temp_dir, 'input.mp4')
            video_bytes = base64.b64decode(video_data)
            with open(video_path, 'wb') as f:
                f.write(video_bytes)
            
            output_path = os.path.join(temp_dir, 'output.mp4')
            
            # Si hay portada, combinarla con el video
            if cover_image_data:
                # Guardar imagen de portada
                cover_path = os.path.join(temp_dir, 'cover.png')
                cover_bytes = base64.b64decode(cover_image_data.split(',')[1] if ',' in cover_image_data else cover_image_data)
                with open(cover_path, 'wb') as f:
                    f.write(cover_bytes)
                
                # Convertir portada a video de 1 segundo (1080x1920)
                cover_video_path = os.path.join(temp_dir, 'cover_video.mp4')
                result = subprocess.run([
                    ffmpeg_cmd, '-y', '-loop', '1', '-i', cover_path,
                    '-c:v', 'libx264', '-t', '1', '-pix_fmt', 'yuv420p',
                    '-vf', 'scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2',
                    cover_video_path
                ], capture_output=True, text=True)
                
                if result.returncode != 0:
                    print(f"Error creando video de portada: {result.stderr}")
                    # Si falla, devolver video original
                    with open(video_path, 'rb') as f:
                        output_data = f.read()
                else:
                    # Usar logo desde frontend (igual que la placa) o generar uno
                    watermark_path = os.path.join(temp_dir, 'watermark.png')
                    if watermark_image_data:
                        wm_bytes = base64.b64decode(watermark_image_data.split(',')[1] if ',' in watermark_image_data else watermark_image_data)
                        with open(watermark_path, 'wb') as f:
                            f.write(wm_bytes)
                    else:
                        create_watermark_image(watermark_path)
                    
                    # Escalar video original a 1080x1920 y agregar marca de agua
                    watermarked_video_path = os.path.join(temp_dir, 'watermarked.mp4')
                    
                    # Escalar video, escalar logo para que se vea bien, overlay, y COPIAR AUDIO
                    # Logo 200x100 -> escalar a ~360px ancho para que se vea nítido en 1080p
                    # -map '[v]' = salida del filter_complex, -map 0:a = audio del input 0
                    result = subprocess.run([
                        ffmpeg_cmd, '-y', '-i', video_path, '-i', watermark_path,
                        '-filter_complex',
                        '[0:v]scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2[v0];'
                        '[1:v]scale=360:-1,format=rgba[w];'
                        '[v0][w]overlay=W-w-40:40:format=auto[vout]',
                        '-c:v', 'libx264', '-preset', 'medium', '-crf', '23', '-pix_fmt', 'yuv420p',
                        '-c:a', 'copy',
                        '-map', '[vout]', '-map', '0:a?',
                        watermarked_video_path
                    ], capture_output=True, text=True)
                    
                    # -map 0:a? no existe en ffmpeg; si no hay audio falla. Intentar sin ? y si falla, sin audio
                    if result.returncode != 0:
                        result = subprocess.run([
                            ffmpeg_cmd, '-y', '-i', video_path, '-i', watermark_path,
                            '-filter_complex',
                            '[0:v]scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2[v0];'
                            '[1:v]scale=360:-1,format=rgba[w];'
                            '[v0][w]overlay=W-w-40:40:format=auto[vout]',
                            '-c:v', 'libx264', '-preset', 'medium', '-crf', '23', '-pix_fmt', 'yuv420p',
                            '-map', '[vout]', '-map', '0:a',
                            watermarked_video_path
                        ], capture_output=True, text=True)
                    
                    if result.returncode != 0:
                        print(f"Con audio falló: {result.stderr}")
                        result = subprocess.run([
                            ffmpeg_cmd, '-y', '-i', video_path, '-i', watermark_path,
                            '-filter_complex',
                            '[0:v]scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2[v0];'
                            '[1:v]scale=360:-1,format=rgba[w];'
                            '[v0][w]overlay=W-w-40:40:format=auto[vout]',
                            '-c:v', 'libx264', '-preset', 'medium', '-crf', '23', '-pix_fmt', 'yuv420p',
                            '-map', '[vout]', '-an',
                            watermarked_video_path
                        ], capture_output=True, text=True)
                    
                    if result.returncode != 0:
                        print(f"Error escalando video: {result.stderr}")
                        # Si falla, devolver video original
                        with open(video_path, 'rb') as f:
                            output_data = f.read()
                    else:
                        # Verificar si el video con marca de agua tiene audio
                        check_watermarked_audio = subprocess.run([
                            ffmpeg_cmd, '-i', watermarked_video_path, '-hide_banner'
                        ], stdout=subprocess.PIPE, stderr=subprocess.STDOUT, text=True)
                        
                        watermarked_has_audio = check_watermarked_audio.stdout and ('Audio:' in check_watermarked_audio.stdout or 'Stream #0:1' in check_watermarked_audio.stdout)
                        
                        # Portada: agregar 1s de silencio en AAC para que concat no pierda audio
                        if watermarked_has_audio:
                            cover_video_with_audio = os.path.join(temp_dir, 'cover_video_audio.mp4')
                            result_cover = subprocess.run([
                                ffmpeg_cmd, '-y', '-i', cover_video_path,
                                '-f', 'lavfi', '-i', 'anullsrc=r=44100:cl=stereo', '-t', '1', '-shortest',
                                '-c:v', 'copy', '-c:a', 'aac', '-b:a', '128k',
                                cover_video_with_audio
                            ], capture_output=True, text=True)
                            if result_cover.returncode != 0:
                                cover_video_with_audio = cover_video_path
                                watermarked_has_audio = False
                        else:
                            cover_video_with_audio = cover_video_path
                        
                        # Concatenar: -c copy para NO re-codificar y no perder audio
                        concat_file = os.path.join(temp_dir, 'concat.txt')
                        with open(concat_file, 'w') as f:
                            f.write(f"file '{cover_video_with_audio}'\n")
                            f.write(f"file '{watermarked_video_path}'\n")
                        
                        result = subprocess.run([
                            ffmpeg_cmd, '-y', '-f', 'concat', '-safe', '0', '-i', concat_file,
                            '-c', 'copy',
                            output_path
                        ], capture_output=True, text=True)
                        
                        if result.returncode != 0:
                            print(f"Error concatenando: {result.stderr}")
                            # Si falla, devolver video original
                            with open(video_path, 'rb') as f:
                                output_data = f.read()
                        else:
                            # Leer video procesado
                            with open(output_path, 'rb') as f:
                                output_data = f.read()
            else:
                # Sin portada, solo escalar video y agregar marca de agua
                watermark_path = os.path.join(temp_dir, 'watermark.png')
                if watermark_image_data:
                    wm_bytes = base64.b64decode(watermark_image_data.split(',')[1] if ',' in watermark_image_data else watermark_image_data)
                    with open(watermark_path, 'wb') as f:
                        f.write(wm_bytes)
                else:
                    create_watermark_image(watermark_path)
                
                result = subprocess.run([
                    ffmpeg_cmd, '-y', '-i', video_path, '-i', watermark_path,
                    '-filter_complex', '[0:v]scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2[v];[v][1:v]overlay=W-w-30:30',
                    '-c:v', 'libx264', '-preset', 'medium', '-crf', '23', '-pix_fmt', 'yuv420p',
                    '-c:a', 'copy', '-map', '0:v', '-map', '0:a',
                    output_path
                ], capture_output=True, text=True)
                
                if result.returncode != 0:
                    result = subprocess.run([
                        ffmpeg_cmd, '-y', '-i', video_path, '-i', watermark_path,
                        '-filter_complex', '[0:v]scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2[v];[v][1:v]overlay=W-w-30:30',
                        '-c:v', 'libx264', '-preset', 'medium', '-crf', '23', '-pix_fmt', 'yuv420p',
                        '-an', output_path
                    ], capture_output=True, text=True)
                
                if result.returncode != 0:
                    print(f"Error procesando video: {result.stderr}")
                    with open(video_path, 'rb') as f:
                        output_data = f.read()
                else:
                    with open(output_path, 'rb') as f:
                        output_data = f.read()
            
            # Devolver como base64
            output_base64 = base64.b64encode(output_data).decode('utf-8')
            
            return jsonify({
                'success': True,
                'video_data': output_base64,
                'size': len(output_data)
            })
    
    except Exception as e:
        print(f"❌ Error procesando video: {str(e)}")
        import traceback
        traceback.print_exc()
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    # Verificar que yt-dlp está instalado
    ytdlp_found = False
    ytdlp_paths = [
        'yt-dlp',
        '/Users/macbookair/Library/Python/3.9/bin/yt-dlp',
        os.path.expanduser('~/Library/Python/3.9/bin/yt-dlp'),
        '/usr/local/bin/yt-dlp'
    ]
    
    for path in ytdlp_paths:
        try:
            if os.path.exists(path):
                subprocess.run([path, '--version'], capture_output=True, check=True)
                print(f"✅ yt-dlp encontrado en: {path}")
                ytdlp_found = True
                break
        except:
            continue
    
    if not ytdlp_found:
        print("❌ ERROR: yt-dlp no está instalado")
        print("📦 Instalá con: pip3 install --user yt-dlp")
        exit(1)
    
    # Obtener puerto de variable de entorno (para Render) o usar 8000 (local)
    port = int(os.environ.get('PORT', 8000))
    
    # Siempre usar 0.0.0.0 para permitir acceso desde otros dispositivos en la red
    host = '0.0.0.0'
    
    print(f"\n🚀 Servidor iniciado en http://localhost:{port}")
    print(f"📱 Abrí: http://localhost:{port}/gensn-video.html")
    print("\n💡 Para acceder desde otro dispositivo:")
    print("   1. Ambos dispositivos deben estar en la misma red WiFi")
    print("   2. Obtené tu IP local con: ifconfig | grep 'inet ' | grep -v 127.0.0.1")
    print(f"   3. Abrí en el otro dispositivo: http://TU_IP:{port}/gensn-video.html")
    print("\n⏹️  Presioná Ctrl+C para detener\n")
    
    app.run(host=host, debug=(port == 8000), port=port)
