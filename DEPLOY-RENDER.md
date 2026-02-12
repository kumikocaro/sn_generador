# Desplegar el backend en Render (para usar en el celular sin la Mac)

**Más simple:** ver **[UN-SOLO-PASO.md](UN-SOLO-PASO.md)** — desplegás y en el celular agregás a pantalla de inicio (PWA). Un solo paso.

---

## Deploy en Render (detalle)

Para usar **Videos SN** desde el celular donde sea, el backend tiene que estar en la nube. Render permite desplegar con Docker y así tenés Python + ffmpeg.

## Pasos

1. **Cuenta en Render**  
   [render.com](https://render.com) → Sign up (con GitHub es rápido).

2. **Conectar el repo y desplegar**  
   - **Opción A (recomendada):** Dashboard → **New** → **Blueprint** → conectá el repo. Render lee el `render.yaml` y crea el servicio con **Docker** (Python + ffmpeg) y hace deploy.  
   - **Opción B:** Dashboard → **New** → **Web Service** → conectá el repo → **Environment**: **Docker** → Create.  
   - **Instance type**: Free (o paid si querés más recursos).

3. **URL**  
   Cuando termine el deploy, te dan una URL tipo:  
   `https://gensn-video-xxxx.onrender.com`

4. **Configurar la app Android**  
   En **android-app/www/index.html** poné esa URL en `backendUrl`:

   ```javascript
   var backendUrl = 'https://gensn-video-xxxx.onrender.com';
   ```

   Luego prepará la app Android (desde la raíz del repo, con Node instalado):

   ```bash
   ./prepare-app-android.sh
   npx cap open android
   ```

   (O desde `android-app`: `./prepare-android.sh` y después `npx cap open android`.)  
   En Android Studio: Run (▶) o Build → Build APK.

## Importante

- En el **plan free** de Render el servicio se “duerme” después de un rato sin uso. La primera petición puede tardar 1–2 minutos en responder; después va rápido.  
- Para producción seria conviene un plan de pago o otro host que no duerma.  
- La **clave de Gemini** está en el frontend (`gensn-video.html`). Si querés no exponerla, habría que mover la generación de texto al backend.

Con esto, la app Android abre tu backend en Render y ya no dependés de la Mac para usar el generador de videos.
