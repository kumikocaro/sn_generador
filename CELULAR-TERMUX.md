# Videos SN en el celular (sin Mac, sin WiFi a la Mac)

Todo corre **en el celular**. La descarga de Instagram la hace el teléfono (tu IP), no un servidor en la nube, así que no te bloquean.

## Qué necesitás

- Android con **Termux** (instalado desde **F-Droid**, no desde Play Store: [F-Droid – Termux](https://f-droid.org/en/packages/com.termux/)).

## Instalación (una vez)

1. Instalá **Termux** desde F-Droid. Abrí Termux.

2. Copiá y pegá **todo** este bloque en Termux y dale Enter:

```bash
pkg update -y && pkg install -y python ffmpeg git
pip install flask flask-cors yt-dlp Pillow
cd ~ && git clone https://github.com/kumikocaro/sn_generador.git && cd sn_generador
python server.py
```

3. Cuando veas algo como `Servidor iniciado en http://localhost:8000`, dejá Termux abierto (no lo cierres).

4. Abrí **Chrome** (o el navegador que uses) en el celular y entrá a:

   **http://127.0.0.1:8000/gensn-video.html**

5. Opcional: en el navegador → menú (⋮) → **Agregar a la pantalla de inicio**. Así tenés un ícono como una app.

Listo: pegás la URL del reel, Descargar y cargar, portada, procesar, compartir. Todo pasa por el celular.

## Cada vez que quieras usarlo

1. Abrí **Termux**.
2. Ejecutá:
   ```bash
   cd ~/sn_generador && python server.py
   ```
3. Dejá Termux abierto y abrí el navegador en **http://127.0.0.1:8000/gensn-video.html** (o tocá el ícono si lo agregaste a la pantalla de inicio).

## Si actualizaste el repo en la Mac

En el celular, en Termux:

```bash
cd ~/sn_generador && git pull && python server.py
```

Así tenés la misma versión que en la Mac.
