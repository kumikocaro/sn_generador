# Render: qué subir y cómo, paso a paso

Render no es “subir un archivo”. Render **usa el código que está en GitHub** (o GitLab). Así que en la práctica hay dos partes: **1) Subir tu carpeta del proyecto a GitHub.** **2) Decirle a Render que use ese repo.**

---

## Parte 1: Subir tu proyecto a GitHub

Tu proyecto es la carpeta **Sn** (donde están `server.py`, `gensn-video.html`, `Dockerfile`, etc.). Eso es lo que tiene que estar en GitHub.

### Si todavía no tenés el proyecto en GitHub

1. **Creá una cuenta en GitHub**  
   Entrá a [github.com](https://github.com) y registrate (si no tenés cuenta).

2. **Creá un repositorio nuevo (vacío)**  
   - En GitHub: clic en **“New”** (o **“+”** → **“New repository”**).  
   - **Repository name:** por ejemplo `sn-videos` (el nombre que quieras).  
   - Dejalo **vacío**: no marques “Add a README”.  
   - Clic en **“Create repository”**.

3. **Subí la carpeta del proyecto desde tu Mac**  
   En la terminal (en tu Mac), entrá a la carpeta del proyecto y ejecutá (reemplazá `TU_USUARIO` y `sn-videos` por tu usuario de GitHub y el nombre del repo que creaste):

   ```bash
   cd /Users/macbookair/Documents/Sn
   git init
   git add .
   git commit -m "Proyecto Videos SN"
   git branch -M main
   git remote add origin https://github.com/TU_USUARIO/sn-videos.git
   git push -u origin main
   ```

   Te va a pedir usuario y contraseña de GitHub (o un token). Cuando termine, **tu proyecto ya está “subido” a GitHub**: Render va a usar exactamente eso.

### Si ya tenés el proyecto en GitHub

No tenés que “subir” nada aparte: Render va a usar ese mismo repo. Pasá a la **Parte 2**.

---

## Parte 2: Conectar GitHub a Render y desplegar

Acá le decís a Render: “usá este repo de GitHub y poné el servidor en marcha”.

1. **Entrá a Render**  
   [render.com](https://render.com) → **Sign up** o **Log in**.  
   Lo más fácil es **“Sign up with GitHub”** (así Render ya ve tus repos).

2. **Crear el servicio**  
   - En el dashboard de Render: **“New +”** → **“Blueprint”**.  
   - Si te pide “Connect a repository”, elegí **GitHub** y autorizá a Render para ver tus repos.  
   - Buscá y elegí el repo donde subiste el proyecto (ej. `sn-videos`).  
   - Clic en **“Connect”** (o **“Apply”** según lo que diga la pantalla).

3. **Deploy automático**  
   - Render lee el archivo **`render.yaml`** que está en tu proyecto y arma un “Web Service” con **Docker** (así tenés Python + ffmpeg).  
   - No tenés que elegir “qué subir”: Render **descarga solo** el código del repo que conectaste.  
   - Clic en **“Apply”** o **“Create”** y esperá a que termine el deploy (unos minutos).

4. **La URL**  
   Cuando termine, arriba del servicio te va a salir una URL, por ejemplo:  
   `https://gensn-video-xxxx.onrender.com`  
   Esa es la que abrís en el celular y después agregás a la pantalla de inicio.

---

## Resumen: “¿Qué tengo que subir a Render?”

- **No subís archivos a mano a Render.**  
- Lo que “subís” es **tu proyecto a GitHub** (la carpeta Sn con todo lo que tiene: `server.py`, `gensn-video.html`, `Dockerfile`, `render.yaml`, etc.).  
- Después **conectás ese repo de GitHub a Render** y Render usa ese código para armar y correr el servidor.  
- Cada vez que hagas `git push` a ese repo, Render puede volver a desplegar solo (si dejaste el deploy automático).

Si querés, en el siguiente mensaje me decís si ya tenés el proyecto en GitHub o no y te digo solo los pasos que te faltan (por ejemplo solo Parte 1 o solo Parte 2).
