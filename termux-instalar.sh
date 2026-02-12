#!/bin/bash
# Instalación en el CELULAR (Android) con Termux.
# Todo corre en el teléfono: la descarga de Instagram usa TU IP del celular, no un servidor.
#
# 1) Instalá Termux desde F-Droid (no Play Store): https://f-droid.org/en/packages/com.termux/
# 2) Abrí Termux y ejecutá (copiá y pegá todo):
#
#    pkg update -y && pkg install -y python ffmpeg git
#    pip install flask flask-cors yt-dlp Pillow
#    cd ~ && git clone https://github.com/kumikocaro/sn_generador.git && cd sn_generador
#    python server.py
#
# 3) Abrí el NAVEGADOR del celular y entrá a:  http://127.0.0.1:8000/gensn-video.html
# 4) Opcional: en el navegador → menú → Agregar a la pantalla de inicio (queda como app).
#
# La próxima vez: abrí Termux, ejecutá:
#    cd ~/sn_generador && python server.py
# y después abrí el navegador en  http://127.0.0.1:8000/gensn-video.html

echo "Ejecutá los comandos de arriba en Termux (no este script en la Mac)."
