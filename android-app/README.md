# Videos SN – App Android

App Android para el generador de videos SN. **No dependés de la Mac**: el backend corre en la nube (Render) y la app solo abre esa URL en fullscreen.

## Qué necesitás

1. **Backend en la nube** (una sola vez). Así no tenés que tener la Mac prendida.
2. **Android Studio** (para compilar la app o generar el APK).
3. **Node.js** (para Capacitor).

## 1. Desplegar el backend (para usarlo donde sea)

Sin esto la app no puede descargar ni procesar nada.

- Entrá a [render.com](https://render.com) y creá una cuenta.
- Creá un **Web Service** nuevo:
  - Conectá el repo de este proyecto (o subí el código).
  - **Build command:** `pip install -r requirements.txt`
  - **Start command:** `python server.py`
  - Elegí **Docker** como entorno (no “Python”) y que use el **Dockerfile** del repo. Ese Dockerfile ya incluye Python + ffmpeg.
- Render te da una URL tipo: `https://gensn-video.onrender.com`. Ver **DEPLOY-RENDER.md** en la raíz del proyecto para el paso a paso.

Cuando tengas la URL del backend, anotala.

## 2. Configurar la app con tu URL

En **android-app/www/index.html** cambiá la variable `backendUrl` por la URL de tu backend (la de Render), sin barra final:

```javascript
var backendUrl = 'https://TU-APP.onrender.com';
```

O dejá `https://gensn-video.onrender.com` si ya desplegaste con ese nombre.

## 3. Instalar y abrir el proyecto Android

Desde la **raíz del repo** (con Node.js instalado):

```bash
./prepare-app-android.sh
cd android-app && npx cap open android
```

O manualmente desde `android-app`:

```bash
cd android-app
./prepare-android.sh
npx cap open android
```

Se abre Android Studio. Ahí podés:

- **Run** (play) para probar en emulador o celular conectado por USB.
- **Build → Build Bundle(s) / APK(s) → Build APK(s)** para generar el APK e instalarlo donde quieras.

## 4. Uso

- Abrís la app en el celular.
- La app redirige a tu backend (Render) y cargás **gensn-video** ahí.
- Descargás el reel, armás la portada, procesás y compartís. Todo pasa por el servidor en la nube, no por la Mac.

## Cambiar la URL del backend después

- Editá **www/index.html** y cambiá `backendUrl`.
- Volvé a sincronizar y compilar:

```bash
npx cap sync android
```

Luego en Android Studio: Run o generar de nuevo el APK.

## Resumen

| Dónde | Rol |
|-------|-----|
| **Render** | Backend (Flask, yt-dlp, ffmpeg). Tiene que estar desplegado y con ffmpeg disponible. |
| **App Android** | Abre tu backend en fullscreen. No hace falta tener la Mac prendida. |

Si querés que todo funcione **solo en el celular** (sin servidor en la nube), habría que pasar la lógica de procesamiento a la app con FFmpeg en Android (por ejemplo ffmpeg-kit) y usar solo “subir video desde galería”; la descarga desde Instagram en el celular es más compleja. Por ahora esta app + backend en Render te deja usar el flujo completo desde cualquier lado.
