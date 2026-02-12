# 🔍 Configurar Google Custom Search para Imágenes de Noticias

## ⚠️ IMPORTANTE
El archivo `gensn-universal.html` ahora busca imágenes **reales de noticias actuales** en Google Images, NO fotos de stock.

---

## 📋 Configuración Necesaria

### 1️⃣ **Crear Custom Search Engine**

1. Ve a: https://programmablesearchengine.google.com/controlpanel/create
2. **Nombre**: "Búsqueda de Imágenes de Noticias"
3. **Qué buscar**: Selecciona "Buscar en toda la web"
4. **Configuración de imágenes**: ACTIVAR "Búsqueda de imágenes"
5. Click en **Crear**

### 2️⃣ **Configurar Filtros de Búsqueda**

Después de crear:

1. Click en **Editar motor de búsqueda**
2. En la pestaña **"Configuración básica"**:
   - Activar: ✅ "Búsqueda de imágenes"
   - Activar: ✅ "Búsqueda en toda la web"
3. En la pestaña **"Configuración avanzada"**:
   - SafeSearch: **Desactivado** (para noticias actuales)
   - Restricción de sitios: **Dejar vacío** (busca en toda la web)

### 3️⃣ **Obtener el ID del Motor de Búsqueda**

1. En el panel de control, busca: **"ID del motor de búsqueda"**
2. Copia el ID (formato: `67e0cf6f4cd5f4a85`)
3. Reemplázalo en `gensn-universal.html`:

```javascript
const GOOGLE_SEARCH_ENGINE_ID = 'TU_ID_AQUI';
```

### 4️⃣ **API Key de Google**

Ya está configurada la misma API key de Gemini:
```javascript
const GOOGLE_SEARCH_API_KEY = 'AIzaSyABAsuYV7hJUWx4UDNyVa0dkEZRm2OrbvI';
```

**Límites:**
- ✅ **100 búsquedas GRATIS por día**
- Si necesitas más: https://console.cloud.google.com/apis/api/customsearch.googleapis.com

---

## 🎯 Cómo Funciona Ahora

1. **Usuario pega URL** de noticia
2. **Sistema extrae título** (ej: "Milei anuncia nuevas medidas económicas")
3. **Busca en Google Images**: `"Milei anuncia nuevas medidas económicas noticia actualidad"`
4. **Filtros aplicados**:
   - ✅ Solo imágenes grandes
   - ✅ Solo de la última semana (`dateRestrict=w1`)
   - ✅ De sitios de noticias reales
5. **Devuelve 10 imágenes** relacionadas con la noticia

---

## 🔧 Parámetros de Búsqueda

En el código (`gensn-universal.html`):

```javascript
const searchUrl = `https://www.googleapis.com/customsearch/v1?
  key=${GOOGLE_SEARCH_API_KEY}&
  cx=${GOOGLE_SEARCH_ENGINE_ID}&
  q=${encodeURIComponent(newsQuery)}&
  searchType=image&          // Solo imágenes
  num=10&                    // 10 resultados
  imgSize=large&             // Solo imágenes grandes
  dateRestrict=w1&           // Última semana
  safe=off                   // Sin filtro (para noticias)
`;
```

**Puedes ajustar:**
- `dateRestrict`: 
  - `d1` = último día
  - `w1` = última semana
  - `m1` = último mes
- `num`: Cantidad de imágenes (máx. 10)
- `imgSize`: `large`, `medium`, `small`

---

## ✅ Testing

Para probar si funciona:

1. Abre la consola del navegador (F12)
2. Pega una URL de noticia
3. Mira los logs:
   ```
   🔍 Buscando imágenes de noticias para: Milei anuncia...
   📷 Imágenes de noticias encontradas: 10
   ```

Si vez error `403` o `quota exceeded`:
- Verifica que la API key tenga habilitado "Custom Search API"
- Chequea que no hayas excedido las 100 búsquedas diarias

---

## 💰 Costos

- ✅ **GRATIS**: 100 búsquedas/día
- 💵 **Pago**: $5 USD por cada 1000 búsquedas adicionales

Para un medio con ~15 noticias/día = **450 búsquedas/mes** → Todo **GRATIS**

---

## 🆘 Alternativas Si No Funciona

Si Google Custom Search da problemas, puedes usar el **fallback automático**:

El código ya tiene fallback que usa las imágenes del post original si Google falla.

---

## 📞 Soporte

Si necesitas ayuda:
- Documentación oficial: https://developers.google.com/custom-search/v1/overview
- Console de APIs: https://console.cloud.google.com/
