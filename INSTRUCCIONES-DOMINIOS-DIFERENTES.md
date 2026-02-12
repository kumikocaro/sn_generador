# 🌐 Instrucciones para Dominios Diferentes

## 📍 Tu Situación Actual

- **WordPress:** `www.saltanews.com.ar`
- **HTML:** `www.kumodev.com/sn/gensn.html`
- **Problema:** CORS (Cross-Origin Resource Sharing) bloquea las llamadas entre dominios

---

## ✅ SOLUCIÓN IMPLEMENTADA: CORS Configurado

He actualizado el plugin para permitir llamadas desde `kumodev.com` a `saltanews.com.ar`.

### 📝 Pasos a Seguir:

#### 1. Subir el Plugin Actualizado

El archivo `sn-auto-placas.php` ya tiene configurado:
- ✅ Headers CORS para permitir `kumodev.com`
- ✅ URL del email apuntando a `kumodev.com/sn/gensn.html`

**Solo necesitas:**
1. Subir `sn-auto-placas.php` a `/wp-content/plugins/sn-auto-placas/`
2. Activar el plugin en WordPress

#### 2. Verificar el HTML

El archivo `sn2.html` ya tiene configurado:
- ✅ `YOUR_WEBSITE_URL = 'https://www.saltanews.com.ar'`

**Solo necesitas:**
1. Subir `sn2.html` a `kumodev.com/sn/` (renómbralo a `gensn.html` si es necesario)

#### 3. Probar CORS

Abre la consola del navegador (F12) y verifica que no haya errores de CORS.

---

## 🔧 Configuración Detallada

### En el Plugin (`sn-auto-placas.php`)

**Línea ~42:** URL del email (ya configurada)
```php
$share_url = 'https://www.kumodev.com/sn/gensn.html?auto=' . $post_id . '&token=' . $token;
```

**Líneas ~96-120:** Headers CORS (ya configurados)
```php
header('Access-Control-Allow-Origin: https://www.kumodev.com');
```

### En el HTML (`sn2.html`)

**Línea ~171:** URL de WordPress (ya configurada)
```javascript
const YOUR_WEBSITE_URL = 'https://www.saltanews.com.ar';
```

---

## 🧪 Cómo Probar

1. **Publica un post** en WordPress (`saltanews.com.ar`)
2. **Revisa tu email** - deberías recibir el enlace
3. **Haz clic en el enlace** - debería abrir `kumodev.com/sn/gensn.html?auto=ID&token=TOKEN`
4. **Abre la consola** (F12) y verifica:
   - ✅ No debe haber errores de CORS
   - ✅ Debe cargar los datos del post
   - ✅ Debe generar la placa automáticamente

---

## ⚠️ Si Aún Tienes Problemas de CORS

### Opción A: Agregar en `.htaccess` de WordPress

Si el plugin no funciona, agrega esto en el `.htaccess` de WordPress:

```apache
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "https://www.kumodev.com"
    Header set Access-Control-Allow-Methods "GET, OPTIONS"
    Header set Access-Control-Allow-Credentials "true"
</IfModule>
```

### Opción B: Usar un Proxy en el HTML

Si CORS sigue fallando, puedes crear un proxy PHP en `kumodev.com`:

**Crear archivo:** `kumodev.com/sn/proxy.php`
```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$post_id = $_GET['id'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($post_id) || empty($token)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros faltantes']);
    exit;
}

$url = "https://www.saltanews.com.ar/wp-json/placas/v1/auto-post/{$post_id}?token={$token}";
$response = @file_get_contents($url);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener datos']);
    exit;
}

echo $response;
?>
```

Luego en el HTML, cambia la línea ~241:
```javascript
// Cambiar de:
const response = await fetch(`${YOUR_WEBSITE_URL}/wp-json/placas/v1/auto-post/${autoPostId}?token=${autoToken}`);

// A:
const response = await fetch(`https://www.kumodev.com/sn/proxy.php?id=${autoPostId}&token=${autoToken}`);
```

---

## 🎯 Opción Alternativa: Mover HTML al Mismo Hosting

Si prefieres evitar problemas de CORS completamente:

### Ventajas:
- ✅ No necesitas configurar CORS
- ✅ Más rápido (mismo servidor)
- ✅ Menos problemas de seguridad

### Pasos:
1. **Copia** `sn2.html` a `saltanews.com.ar/sn/gensn.html`
2. **Actualiza** la URL en el plugin (línea ~42):
   ```php
   $share_url = home_url('/sn/gensn.html?auto=' . $post_id . '&token=' . $token);
   ```
3. **Elimina** los headers CORS del plugin (no son necesarios)

---

## 📋 Checklist

- [ ] Plugin subido a WordPress con CORS configurado
- [ ] HTML subido a `kumodev.com/sn/`
- [ ] URL en plugin apunta a `kumodev.com`
- [ ] URL en HTML apunta a `saltanews.com.ar`
- [ ] Probado con un post de prueba
- [ ] Verificado que no hay errores de CORS en consola
- [ ] Email recibido correctamente
- [ ] Enlace del email funciona

---

## 🐛 Solución de Problemas

### Error: "CORS policy: No 'Access-Control-Allow-Origin' header"

**Solución:**
1. Verifica que el plugin esté activado
2. Verifica que los headers CORS estén en el código
3. Prueba agregar el `.htaccess` (Opción A arriba)

### Error: "Network request failed"

**Solución:**
1. Verifica que la URL de WordPress sea correcta
2. Verifica que la REST API esté funcionando: `saltanews.com.ar/wp-json/`
3. Verifica los Permalinks de WordPress

### El email no llega

**Solución:**
1. Verifica el email del administrador en WordPress
2. Revisa la carpeta de spam
3. Verifica que WordPress pueda enviar emails

---

## ✅ Resumen

**NO necesitas ponerlos en el mismo hosting.** El plugin ya está configurado para trabajar con dominios diferentes usando CORS.

Solo necesitas:
1. ✅ Subir el plugin a WordPress
2. ✅ Subir el HTML a kumodev.com
3. ✅ Probar con un post

¡Todo debería funcionar! 🎉
