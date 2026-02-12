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

### Opción A: Un solo toque (recomendado)

Instalá **Termux:Widget** desde F-Droid: [Termux:Widget](https://f-droid.org/en/packages/com.termux.widget/).

En Termux, ejecutá **una vez** esto para crear el acceso directo:

```bash
mkdir -p ~/.shortcuts
cat > ~/.shortcuts/videos-sn << 'EOF'
#!/bin/bash
cd ~/sn_generador
# Si el servidor ya está corriendo, solo abrimos el navegador
python3 -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8000/', timeout=1)" 2>/dev/null && {
  am start -a android.intent.action.VIEW -d "http://127.0.0.1:8000/gensn-video.html"
  exit 0
}
nohup python3 server.py > /dev/null 2>&1 &
sleep 2
am start -a android.intent.action.VIEW -d "http://127.0.0.1:8000/gensn-video.html"
EOF
chmod +x ~/.shortcuts/videos-sn
```

Después: en la pantalla de inicio del celular, **mantené apretado** → Widgets → buscá **Termux** → arrastrá el widget "Shortcut" y elegí **videos-sn**.

De ahí en más: **tocás ese ícono** y se abre el navegador en Videos SN (y si el servidor no estaba corriendo, lo inicia solo). No tenés que abrir Termux ni escribir nada.

---

### Opción B: Manual

1. Abrí **Termux**.
2. Ejecutá: `cd ~/sn_generador && python server.py`
3. Abrí el navegador en **http://127.0.0.1:8000/gensn-video.html** (o el ícono si lo agregaste a la pantalla de inicio).

## Si actualizaste el repo en la Mac

En el celular, en Termux:

```bash
cd ~/sn_generador && git pull && python server.py
```

Así tenés la misma versión que en la Mac.
