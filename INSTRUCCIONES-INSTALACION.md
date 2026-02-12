# 📱 Instrucciones de Instalación - Auto Placas Instagram

## 📋 Resumen
Este sistema envía un email automáticamente cada vez que publicas un post en WordPress, con un enlace que abre la placa ya generada y lista para compartir en Instagram.

---

## 🗂️ Archivos Necesarios

1. **sn-auto-placas.php** - Plugin de WordPress
2. **sn2.html** (o gensn.html) - Tu archivo HTML actualizado
3. **manifest.json** - Ya lo tienes creado

---

## 📝 PASO 1: Subir el Plugin a WordPress

### Opción A: Como Plugin (Recomendado)

1. **Conecta a tu hosting** por FTP o cPanel File Manager
2. **Navega a:** `/wp-content/plugins/`
3. **Crea una carpeta nueva:** `sn-auto-placas` (o el nombre que prefieras)
4. **Sube el archivo** `sn-auto-placas.php` dentro de esa carpeta
5. **Ve a tu WordPress Admin** → Plugins
6. **Activa el plugin** "Salta News - Auto Placas Instagram"

### Opción B: En functions.php (Alternativa)

Si prefieres no usar un plugin:

1. **Conecta a tu hosting** por FTP o cPanel
2. **Navega a:** `/wp-content/themes/tu-tema-activo/`
3. **Abre el archivo** `functions.php`
4. **Copia TODO el contenido** de `sn-auto-placas.php` (excepto las primeras líneas del header del plugin)
5. **Pégalo al final** de `functions.php`
6. **Guarda el archivo**

⚠️ **IMPORTANTE:** Si usas esta opción, elimina estas líneas del código:
```php
/**
 * Plugin Name: ...
 * Description: ...
 * Version: 1.0
 * Author: Salta News
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}
```

---

## 📝 PASO 2: Subir el HTML Actualizado

1. **Conecta a tu hosting** por FTP o cPanel
2. **Navega a la carpeta** donde tienes tu HTML (probablemente `/sn/` o `/public_html/sn/`)
3. **Haz una copia de seguridad** de tu archivo actual (`gensn.html` o como se llame)
4. **Sube el archivo** `sn2.html` actualizado
5. **Si tu archivo se llama diferente** (ej: `gensn.html`), renombra `sn2.html` a ese nombre

---

## 📝 PASO 3: Verificar la URL en el Plugin

1. **Abre el archivo** `sn-auto-placas.php` en tu editor
2. **Busca esta línea** (aproximadamente línea 40):
```php
$share_url = home_url('/sn/gensn.html?auto=' . $post_id . '&token=' . $token);
```
3. **Ajusta la ruta** según donde tengas tu HTML:
   - Si está en `/sn/gensn.html` → Déjalo como está
   - Si está en `/gensn.html` → Cambia a `home_url('/gensn.html?auto=...`
   - Si está en otra carpeta → Ajusta la ruta

---

## 📝 PASO 4: Verificar Permalinks de WordPress

1. **Ve a WordPress Admin** → Configuración → Enlaces permanentes
2. **Asegúrate** de que no esté en "Simple"
3. **Selecciona cualquier otra opción** (ej: "Nombre de la entrada")
4. **Guarda los cambios**

Esto es necesario para que la REST API funcione correctamente.

---

## 📝 PASO 5: Probar el Sistema

### Prueba Manual:

1. **Crea un post de prueba** en WordPress
2. **Asegúrate** de que tenga:
   - ✅ Título
   - ✅ Contenido
   - ✅ Imagen destacada (recomendado)
3. **Publica el post**
4. **Revisa tu email** (el que configuraste como admin en WordPress)
5. **Deberías recibir** un email con un botón "Abrir y Compartir en Instagram"
6. **Haz clic en el enlace**
7. **Verifica** que:
   - La placa se carga automáticamente
   - El texto por IA se genera automáticamente
   - El texto se copia al portapapeles
   - Puedes compartir en Instagram

---

## 🔧 Configuración Adicional

### Cambiar el Email de Notificaciones

Por defecto, se envía al email del administrador de WordPress. Para cambiarlo:

1. **Abre** `sn-auto-placas.php`
2. **Busca esta línea:**
```php
$admin_email = get_option('admin_email');
```
3. **Cámbiala por:**
```php
$admin_email = 'tu-email@ejemplo.com';
```

### Cambiar el Tiempo de Expiración del Token

Por defecto, los tokens expiran en 24 horas. Para cambiarlo:

1. **Abre** `sn-auto-placas.php`
2. **Busca esta línea:**
```php
update_post_meta($post_id, '_sn_share_expires', time() + (24 * 60 * 60));
```
3. **Cambia `24`** por las horas que quieras (ej: `48` para 2 días)

---

## 🐛 Solución de Problemas

### El email no llega

1. **Verifica** que WordPress pueda enviar emails:
   - Ve a WordPress Admin → Configuración → General
   - Verifica el email del administrador
2. **Revisa la carpeta de spam**
3. **Instala un plugin de email** como "WP Mail SMTP" si es necesario

### El enlace no carga los datos

1. **Verifica** que la REST API esté funcionando:
   - Ve a: `tu-sitio.com/wp-json/`
   - Deberías ver un JSON con información
2. **Verifica** que el token no haya expirado (24 horas)
3. **Revisa la consola del navegador** (F12) para ver errores

### La imagen no se carga

1. **Verifica** que el post tenga imagen destacada
2. **Verifica** que la imagen sea accesible públicamente
3. **Revisa** los permisos de archivos en el servidor

### Error 403 (Token inválido)

1. **Verifica** que el enlace del email no se haya modificado
2. **Verifica** que el token no haya expirado
3. **Intenta** generar un nuevo post para obtener un nuevo token

---

## 📁 Estructura de Archivos Final

```
tu-hosting/
├── wp-content/
│   └── plugins/
│       └── sn-auto-placas/
│           └── sn-auto-placas.php
└── sn/ (o donde tengas tu HTML)
    ├── gensn.html (o sn2.html)
    └── manifest.json
```

---

## ✅ Checklist de Instalación

- [ ] Plugin subido a `/wp-content/plugins/sn-auto-placas/`
- [ ] Plugin activado en WordPress
- [ ] HTML actualizado subido al servidor
- [ ] URL en el plugin ajustada correctamente
- [ ] Permalinks de WordPress configurados
- [ ] Email de administrador verificado
- [ ] Prueba realizada con un post de prueba
- [ ] Email recibido correctamente
- [ ] Enlace del email funciona
- [ ] Placa se carga automáticamente
- [ ] Texto por IA se genera automáticamente
- [ ] Compartir en Instagram funciona

---

## 🎉 ¡Listo!

Una vez completados todos los pasos, cada vez que publiques un post en WordPress:

1. ✅ Recibirás un email automáticamente
2. ✅ El enlace abrirá la placa ya generada
3. ✅ El texto por IA estará listo
4. ✅ Solo necesitas tocar "Compartir" y seleccionar Instagram

---

## 📞 Soporte

Si tienes problemas, verifica:
- Los logs de errores de WordPress
- La consola del navegador (F12)
- Los logs del servidor

---

**Versión:** 1.0  
**Última actualización:** 2024
