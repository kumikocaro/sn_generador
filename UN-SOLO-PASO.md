# Un solo paso para usarlo en el celular (sin Mac, sin Android Studio)

## Lo único que tenés que hacer

**Desplegar el proyecto en Render.** Nada de compilar app ni editar archivos.

**¿Qué subo y cómo?** → Guía detallada: **[RENDER-PASO-A-PASO.md](RENDER-PASO-A-PASO.md)** (subir la carpeta a GitHub y conectar ese repo a Render).

Resumen rápido:
1. Subís **toda la carpeta del proyecto** (esta carpeta Sn) a **GitHub** (creás un repo y hacés `git push`).
2. Entrás a **[render.com](https://render.com)** → **New** → **Blueprint** → conectás ese repo de GitHub.
3. Render usa el código del repo y el `render.yaml` / `Dockerfile` para armar el servidor. Dale **Apply**.
4. Cuando termine, te dan una URL, por ejemplo: `https://gensn-video-xxxx.onrender.com`

---

## En el celular

1. Abrí esa URL en el navegador del celular.
2. Tocá el menú del navegador (⋮ o “Compartir”) y elegí **“Agregar a la pantalla de inicio”** (o “Add to Home Screen”).
3. Listo. Te queda un ícono como una app; cuando lo tocás se abre el generador de videos. Todo corre en la nube, no necesitás la Mac.

---

**Resumen:** un solo paso = desplegar en Render. En el celular solo abrís la URL y la agregás a la pantalla de inicio. No hace falta Android Studio, ni Node, ni poner la URL en ningún archivo (porque entrás directo a tu backend y las llamadas a la API son al mismo servidor).
