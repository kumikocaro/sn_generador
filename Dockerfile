# Para desplegar en Render (o cualquier host con Docker): Python + ffmpeg
FROM python:3.11-slim

RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY . .

EXPOSE 8000
# Render suele inyectar PORT
ENV PORT=8000
CMD python server.py
