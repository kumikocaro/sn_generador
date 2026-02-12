# App Android (opcional)

**¿Querés el camino corto?** → **Leé [UN-SOLO-PASO.md](UN-SOLO-PASO.md).** Desplegás en Render y en el celular agregás la página a la pantalla de inicio (PWA). Un solo paso, sin Android Studio.

Lo de abajo es solo si querés **generar un APK** (app instalable desde archivo) en vez de usar la PWA.

---

## 1. Desplegar el backend en Render (una vez)

- Entrá a [render.com](https://render.com) y conectá este repo (GitHub/GitLab).
- **New** → **Blueprint** (o **Web Service** con **Docker**).
- Dejá que haga el deploy. El `render.yaml` y el `Dockerfile` ya están listos (Python + ffmpeg).
- Copiá la URL que te dan, ej: `https://gensn-video-xxxx.onrender.com`

---

## 2. Poner esa URL en la app (una vez)

En **android-app/www/index.html** cambiá la línea:

```javascript
var backendUrl = 'https://gensn-video.onrender.com';
```

por tu URL de Render (la que copiaste), **sin barra final**.

---

## 3. Generar la app Android (en tu Mac, con Node instalado)

En la terminal, desde la carpeta del proyecto:

```bash
./prepare-app-android.sh
cd android-app && npx cap open android
```

Se abre Android Studio. Ahí: **Run** en el celular o emulador, o **Build** → **Build APK(s)** para instalar el APK donde quieras.

---

## Resumen

| Paso | Dónde | Qué |
|------|--------|-----|
| 1 | Render (web) | Conectar repo → Deploy con Docker → Anotar URL |
| 2 | `android-app/www/index.html` | Poner tu URL en `backendUrl` |
| 3 | Tu Mac (terminal) | `./prepare-app-android.sh` y `npx cap open android` |

Después de eso usás la app en el celular y no dependés de la Mac para nada.
