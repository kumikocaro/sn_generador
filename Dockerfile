# Render (plan free): Python + ffmpeg. Build más liviano.
FROM python:3.11-slim

RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
COPY . .

EXPOSE 8000
ENV PORT=8000
# Render inyecta PORT; Flask debe escuchar 0.0.0.0 (ya lo hace server.py)
CMD ["python", "server.py"]
