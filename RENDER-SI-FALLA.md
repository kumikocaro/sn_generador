# Si el deploy en Render sigue fallando

1. **Mirá el error concreto**  
   En Render: entrá al servicio **gensn-video** → pestaña **Logs** o **Events**. Ahí sale si falló el **build** (Docker) o el **start** (Python).

2. **Build falla**  
   Suele ser tiempo o memoria en plan free. Probá hacer **Manual sync** de nuevo; con `.dockerignore` el build es más liviano.

3. **Start falla**  
   Revisá que en Logs no diga "No open ports" o error de Python. El servidor ya usa `PORT` y `0.0.0.0`.

4. **Plan free + Docker**  
   A veces Render limita Docker en free. Si siempre falla, en el dashboard del servicio → **Settings** → revisá si podés cambiar a **Native Environment** (Python) y usar un buildpack con ffmpeg; si no, el plan free podría no soportar este stack y habría que probar otro host (Railway, Fly.io, etc.).
