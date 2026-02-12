# 🔧 Solución: Error 404 en Endpoint REST API

## Problema
El endpoint `/wp-json/placas/v1/auto-post/ID?token=TOKEN` devuelve error 404 `rest_no_route`.

## Solución

### Paso 1: Subir el plugin actualizado ✅

1. **Descarga el archivo actualizado:**
   - Archivo: `sn-auto-placas.php`
   - Este archivo ya está optimizado y consolidado

2. **Súbelo a tu hosting WordPress:**
   - Ruta: `/wp-content/plugins/sn-auto-placas/sn-auto-placas.php`
   - **IMPORTANTE:** Reemplaza completamente el archivo anterior

3. **Verifica que el archivo se subió correctamente:**
   - El archivo debe tener aproximadamente 280 líneas
   - Debe contener la función `sn_get_auto_post_data`

---

### Paso 2: Refrescar Permalinks (MUY IMPORTANTE) 🔄

WordPress necesita refrescar las rutas de la REST API. Sigue estos pasos:

1. **Accede al panel de WordPress:**
   - Ve a: `https://www.saltanews.com.ar/wp-admin`

2. **Ve a Configuración → Enlaces permanentes:**
   - En el menú lateral izquierdo, haz clic en **"Configuración"**
   - Luego haz clic en **"Enlaces permanentes"**

3. **Refresca los permalinks:**
   - **NO cambies ninguna configuración**
   - Simplemente haz clic en el botón **"Guardar cambios"** (abajo de la página)
   - Esto fuerza a WordPress a refrescar todas las rutas, incluyendo las de la REST API

---

### Paso 3: Desactivar y Reactivar el Plugin 🔌

Esto fuerza el registro de los endpoints:

1. **Ve a Plugins:**
   - En el menú lateral, haz clic en **"Plugins"**

2. **Desactiva el plugin:**
   - Busca **"Salta News - Auto Placas Instagram"**
   - Haz clic en **"Desactivar"**

3. **Espera 5 segundos**

4. **Activa el plugin de nuevo:**
   - Haz clic en **"Activar"**

---

### Paso 4: Probar los Endpoints 🧪

Prueba estos endpoints en tu navegador (copia y pega la URL completa):

#### 4.1. Verificar que la REST API funciona:
```
https://www.saltanews.com.ar/wp-json/
```
**Resultado esperado:** Debe mostrar un JSON con información de la REST API de WordPress.

#### 4.2. Probar endpoint de prueba del plugin:
```
https://www.saltanews.com.ar/wp-json/placas/v1/test-endpoint
```
**Resultado esperado:**
```json
{"status":"ok","message":"Endpoint funcionando correctamente"}
```

#### 4.3. Probar endpoint de email:
```
https://www.saltanews.com.ar/wp-json/placas/v1/test
```
**Resultado esperado:** JSON con información sobre la configuración de email.

#### 4.4. Probar endpoint de diagnóstico (NUEVO):
```
https://www.saltanews.com.ar/wp-json/placas/v1/debug
```
**Resultado esperado:**
```json
{
  "plugin_activo": true,
  "funciones_disponibles": {
    "sn_get_auto_post_data": true,
    "sn_test_email": true
  },
  "endpoints_registrados": {
    "/placas/v1/auto-post/(?P<id>\\d+)": ["GET"],
    "/placas/v1/test-endpoint": ["GET"],
    "/placas/v1/test": ["GET"],
    "/placas/v1/debug": ["GET"]
  }
}
```
**Si este endpoint muestra que `sn_get_auto_post_data` es `false`, el problema es que la función no está cargada correctamente.**

#### 4.5. Probar endpoint con un post real:

**IMPORTANTE:** Necesitas un POST ID y un TOKEN válido. Para obtenerlos:

1. **Crea o edita un post en WordPress**
2. **Publica el post** (esto generará un email con el token)
3. **Abre el email** y copia la URL completa
4. **Extrae el ID y el token** de la URL:
   - Ejemplo: `https://www.kumodev.com/sn/gensn.html?auto=123&token=abc123...`
   - ID = `123`
   - TOKEN = `abc123...`

5. **Prueba el endpoint:**
```
https://www.saltanews.com.ar/wp-json/placas/v1/auto-post/123?token=abc123...
```
(Reemplaza `123` con el ID real y `abc123...` con el token real)

**Resultado esperado:**
```json
{
  "titulo": "Título del post",
  "contenido": "Contenido del post...",
  "url": "https://www.saltanews.com.ar/...",
  "imagenDataUri": "data:image/jpeg;base64,...",
  "excerpt": "Resumen del post..."
}
```

---

### Paso 5: Usar el Endpoint de Diagnóstico 🔍

**ANTES de continuar con el paso 6, prueba el endpoint de diagnóstico:**

```
https://www.saltanews.com.ar/wp-json/placas/v1/debug
```

**Este endpoint te mostrará:**
- ✅ Si el plugin está activo
- ✅ Si las funciones están disponibles
- ✅ Qué endpoints están registrados

**Si el diagnóstico muestra que `sn_get_auto_post_data` es `false`:**
1. El archivo del plugin no se cargó correctamente
2. Hay un error de sintaxis PHP
3. El plugin no está activado

**Solución:**
- Verifica que el archivo `sn-auto-placas.php` esté completo
- Revisa los logs de WordPress para errores de PHP
- Desactiva y reactiva el plugin

---

### Paso 6: Si Aún No Funciona 🔍

Si después de seguir todos los pasos anteriores el endpoint sigue dando 404:

#### 5.1. Verificar que el plugin está activo:
- Ve a: Plugins → Verifica que "Salta News - Auto Placas Instagram" esté **activado**

#### 5.2. Verificar errores de PHP:
- Activa el modo debug de WordPress:
  - Edita `wp-config.php` en la raíz de WordPress
  - Busca: `define('WP_DEBUG', false);`
  - Cámbialo a: `define('WP_DEBUG', true);`
  - Agrega: `define('WP_DEBUG_LOG', true);`
- Revisa los logs en: `/wp-content/debug.log`

#### 5.3. Verificar permisos de archivos:
- El archivo `sn-auto-placas.php` debe tener permisos de lectura (644 o 755)

#### 5.4. Verificar sintaxis PHP:
- Si hay errores de sintaxis, el plugin no se cargará
- Revisa el log de errores de WordPress o del servidor

#### 5.5. Probar con otro plugin de REST API:
- Instala un plugin como "REST API Log" para ver qué endpoints están registrados

---

### Paso 7: Probar el Flujo Completo 🚀

Una vez que los endpoints funcionen:

1. **Crea un nuevo post en WordPress**
2. **Publica el post**
3. **Revisa tu email** (debe llegar un email con el enlace)
4. **Haz clic en el enlace del email**
5. **Verifica que:**
   - Se abre `gensn.html` automáticamente
   - Se carga la imagen del post
   - Se genera el texto por IA
   - El texto se copia al portapapeles
   - Puedes compartir en Instagram

---

## Resumen de URLs Importantes

- **REST API Base:** `https://www.saltanews.com.ar/wp-json/`
- **Endpoint de prueba:** `https://www.saltanews.com.ar/wp-json/placas/v1/test-endpoint`
- **Endpoint de email:** `https://www.saltanews.com.ar/wp-json/placas/v1/test`
- **Endpoint de diagnóstico:** `https://www.saltanews.com.ar/wp-json/placas/v1/debug` ⭐ NUEVO
- **Endpoint de post:** `https://www.saltanews.com.ar/wp-json/placas/v1/auto-post/{ID}?token={TOKEN}`

---

## Notas Importantes

- ⚠️ **Los tokens expiran en 24 horas** por seguridad
- ⚠️ **Cada post solo genera un email una vez** (para evitar duplicados)
- ⚠️ **Si cambias el plugin, siempre refresca los permalinks**
- ⚠️ **El endpoint requiere HTTPS** (por CORS)

---

## ¿Necesitas Ayuda?

Si después de seguir todos estos pasos el problema persiste:

1. Verifica que el archivo `sn-auto-placas.php` esté completo (debe tener ~280 líneas)
2. Verifica que no haya errores de sintaxis PHP
3. Revisa los logs de WordPress (`/wp-content/debug.log`)
4. Prueba desactivar otros plugins temporalmente para ver si hay conflictos
