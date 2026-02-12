#!/bin/bash
# Script para iniciar el servidor automáticamente

echo "🎬 Iniciando servidor de videos SN..."
echo ""

# Verificar Python
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 no está instalado"
    echo "📦 Descargá de: https://www.python.org/downloads/"
    exit 1
fi

# Verificar dependencias
if ! python3 -c "import flask" &> /dev/null; then
    echo "📦 Instalando dependencias..."
    pip3 install -r requirements.txt
fi

# Iniciar servidor
echo "🚀 Iniciando servidor..."
python3 server.py
