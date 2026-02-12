#!/bin/bash
# Ejecutá este script desde la carpeta android-app (o desde la raíz del repo).
# Requiere: Node.js y npm instalados (https://nodejs.org)

set -e
cd "$(dirname "$0")"

echo "📦 Instalando dependencias..."
npm install

echo ""
echo "🤖 Agregando plataforma Android..."
npx cap add android 2>/dev/null || true

echo ""
echo "🔄 Sincronizando web → Android..."
npx cap sync android

echo ""
echo "✅ Listo. Para abrir en Android Studio:"
echo "   npx cap open android"
echo ""
echo "Después en Android Studio: Run (▶) o Build → Build Bundle(s) / APK(s)."
echo "Recordá cambiar la URL del backend en www/index.html (variable backendUrl)."
