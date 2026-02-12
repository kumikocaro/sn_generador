# 🎬 Generador de Videos SN

Herramienta para descargar videos de Instagram y crear portadas personalizadas con marca de agua.

---

## 🏠 OPCIÓN 1: Uso LOCAL (Recomendado para desarrollo)

### Paso 1: Instalar dependencias

```bash
# Instalar Python (si no lo tenés)
# Descargá de: https://www.python.org/downloads/

# Instalar dependencias
pip3 install -r requirements.txt
```

### Paso 2: Iniciar servidor

```bash
cd /Users/macbookair/Documents/Sn
python3 server.py
```

### Paso 3: Usar la aplicación

1. Abrí tu navegador en: **http://localhost:5000/gensn-video.html**
2. Pegá una URL de Instagram
3. Hace click en "Descargar y Cargar"
4. ¡Listo! 🎉

**Ventajas:**
- ✅ Más rápido
- ✅ Sin límites de CORS
- ✅ Gratis total
- ✅ Control completo

---

## ☁️ OPCIÓN 2: Deploy en RENDER.com (Para producción)

### Paso 1: Crear cuenta en Render

1. Andá a: https://render.com
2. Registrate gratis con GitHub

### Paso 2: Deploy

1. Subí tu código a un repositorio GitHub
2. En Render, hacé click en "New Web Service"
3. Conectá tu repo de GitHub
4. Configuración:
   - **Environment**: Python 3
   - **Build Command**: `pip install -r requirements.txt`
   - **Start Command**: `python server.py`
5. Deploy!

**Tu app estará en**: `https://tu-app.onrender.com`

**Ventajas:**
- ✅ Accesible desde cualquier lugar
- ✅ HTTPS automático
- ✅ Gratis (con sleep después de 15 min)

---

## 📦 Archivos del proyecto

```
Sn/
├── gensn-video.html          # Frontend
├── server.py                 # Backend Python (para local/Render)
├── instagram-proxy.php       # Backend PHP (para hosting tradicional)
├── requirements.txt          # Dependencias Python
└── README.md                 # Este archivo
```

---

## 🔧 Solución de problemas

### "yt-dlp no está instalado"

```bash
pip3 install yt-dlp
```

### "Flask no está instalado"

```bash
pip3 install flask flask-cors
```

### Puerto 5000 ocupado

Cambiá el puerto en `server.py`:
```python
app.run(debug=True, port=8000)  # Usá otro puerto
```

---

## 💡 Tips

- **Para uso diario**: Usá la versión LOCAL (más rápida)
- **Para compartir con el equipo**: Deploy en Render
- **Videos muy grandes**: Pueden tardar en local, pero son más rápidos que en servidor remoto

---

## 🆘 Ayuda

Si tenés problemas, verificá:

1. ✅ Python 3.9+ instalado: `python3 --version`
2. ✅ yt-dlp instalado: `yt-dlp --version`
3. ✅ Flask instalado: `pip3 show flask`

---

**¡Listo! Ahora podés descargar videos de Instagram sin problemas** 🚀
