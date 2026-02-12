# 🚀 Inicio Rápido - Versión Local

## Para empezar AHORA MISMO:

### 1️⃣ Instalar dependencias (solo una vez)

Abrí la Terminal y ejecutá:

```bash
pip3 install flask flask-cors yt-dlp
```

### 2️⃣ Iniciar servidor

```bash
cd /Users/macbookair/Documents/Sn
python3 server.py
```

### 3️⃣ Usar

Abrí tu navegador en: **http://localhost:5000/gensn-video.html**

---

## ✅ Debería aparecer:

```
✅ yt-dlp está instalado

🚀 Servidor iniciado en http://localhost:5000
📱 Abrí: http://localhost:5000/gensn-video.html

⏹️  Presioná Ctrl+C para detener
```

---

## 🎬 Ahora podés:

1. Pegar una URL de Instagram
2. Click en "Descargar y Cargar"
3. ¡El video se descarga automáticamente! 🎉

---

## ❌ Si hay error:

### "pip3: command not found"
```bash
# Instalá Python primero:
# https://www.python.org/downloads/
```

### "yt-dlp: command not found"
```bash
pip3 install yt-dlp
```

### Puerto 5000 ocupado
En `server.py` línea 89, cambiá `5000` por otro número (ej: `8000`)

---

**¡Listo! Es así de simple** 🎉

Para producción (Render), leé el README.md completo.
