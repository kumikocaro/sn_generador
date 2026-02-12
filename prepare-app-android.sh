#!/bin/bash
# Desde la raíz del repo: prepara la app Android (npm install + Capacitor + sync).
# Requiere Node.js instalado: https://nodejs.org
cd "$(dirname "$0")/android-app"
exec ./prepare-android.sh
