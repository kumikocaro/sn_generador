# 🔍 Diagnóstico: Email No Llega

## 🚨 Problema: No recibes el email después de publicar un post

Sigue estos pasos para diagnosticar el problema:

---

## 📋 Paso 1: Verificar que el Plugin Esté Activo

1. **Ve a:** WordPress Admin → Plugins
2. **Busca:** "Salta News - Auto Placas Instagram"
3. **Verifica** que esté **activado** (no solo instalado)

---

## 📋 Paso 2: Probar el Endpoint de Prueba

He agregado un endpoint de prueba. **Abre en tu navegador:**

```
https://www.saltanews.com.ar/wp-json/placas/v1/test
```

**Deberías ver un JSON con:**
```json
{
  "email_configurado": true,
  "email": "tu-email@ejemplo.com",
  "wp_mail_disponible": true,
  "plugin_activo": true,
  "email_prueba_enviado": true
}
```

**Si `email_prueba_enviado` es `false`, WordPress no puede enviar emails.**

---

## 📋 Paso 3: Verificar el Email Configurado

1. **Ve a:** WordPress Admin → Configuración → General
2. **Verifica** el "Dirección de correo electrónico"
3. **Asegúrate** de que sea un email válido y accesible

---

## 📋 Paso 4: Verificar Logs de WordPress (Si WP_DEBUG está activado)

Si tienes `WP_DEBUG` activado, revisa los logs:

1. **Busca** en los logs mensajes como:
   - `SN Auto Placas: Hook ejecutado para post ID: X`
   - `SN Auto Placas: Enviando email a: email@ejemplo.com`
   - `SN Auto Placas: Email enviado exitosamente`

2. **Si ves errores**, cópialos y revísalos

**Para activar WP_DEBUG temporalmente:**
1. Abre `wp-config.php`
2. Busca `define('WP_DEBUG', false);`
3. Cámbialo a `define('WP_DEBUG', true);`
4. Agrega: `define('WP_DEBUG_LOG', true);`
5. Los logs estarán en `/wp-content/debug.log`

---

## 📋 Paso 5: Verificar que el Post Sea Nuevo

**IMPORTANTE:** El plugin solo envía emails cuando:
- ✅ El post se **publica por primera vez**
- ✅ El post **no tiene** el meta `_sn_placa_sent` en 'yes'

**Si el post ya existía antes de activar el plugin:**
1. **Edita** el post
2. **Cambia** el estado a "Borrador"
3. **Guarda**
4. **Vuelve a publicar**

O **elimina el meta manualmente:**
1. Instala un plugin como "Advanced Custom Fields" o usa phpMyAdmin
2. Busca en `wp_postmeta` el post_id
3. Elimina la fila donde `meta_key = '_sn_placa_sent'`

---

## 📋 Paso 6: Probar con un Post Completamente Nuevo

1. **Crea un post nuevo** desde cero
2. **Agrega:**
   - ✅ Título
   - ✅ Contenido
   - ✅ Imagen destacada (recomendado)
3. **Publica** el post
4. **Revisa** el email (y la carpeta de spam)

---

## 📋 Paso 7: Verificar que WordPress Pueda Enviar Emails

### Problema Común: WordPress no puede enviar emails

**Solución:** Instala un plugin de SMTP

1. **Instala:** "WP Mail SMTP" o "Easy WP SMTP"
2. **Configura** con tu proveedor de email:
   - Gmail
   - Outlook
   - Tu proveedor de hosting
3. **Prueba** el envío desde el plugin
4. **Vuelve a probar** publicando un post

---

## 📋 Paso 8: Revisar Carpeta de Spam

1. **Revisa** la carpeta de spam/correo no deseado
2. **Busca** el asunto: "📱 Nueva placa lista para Instagram"
3. **Si está ahí:**
   - Márcalo como "No es spam"
   - Agrega el remitente a contactos

---

## 📋 Paso 9: Verificar Permisos del Servidor

Algunos servidores bloquean `mail()` de PHP. Verifica con tu proveedor de hosting.

---

## 🔧 Soluciones Rápidas

### Solución 1: Forzar Reenvío para un Post Específico

Agrega esto temporalmente en `functions.php` o en el plugin:

```php
// SOLO PARA PRUEBAS - Eliminar después
add_action('admin_init', function() {
    if (isset($_GET['sn_force_send']) && current_user_can('manage_options')) {
        $post_id = intval($_GET['post_id']);
        if ($post_id > 0) {
            // Eliminar el flag de enviado
            delete_post_meta($post_id, '_sn_placa_sent');
            // Obtener el post
            $post = get_post($post_id);
            if ($post) {
                // Forzar ejecución
                sn_auto_generate_placa($post_id, $post);
                echo "Email forzado para post ID: $post_id";
                exit;
            }
        }
    }
});
```

Luego visita: `tu-sitio.com/wp-admin/?sn_force_send=1&post_id=123`

**⚠️ Elimina este código después de probar**

---

### Solución 2: Instalar Plugin SMTP (Recomendado)

1. **Instala:** "WP Mail SMTP" desde el repositorio de WordPress
2. **Configura** con Gmail, Outlook o tu proveedor
3. **Prueba** el envío
4. **Vuelve a probar** publicando un post

---

## 📊 Checklist de Diagnóstico

- [ ] Plugin activado en WordPress
- [ ] Endpoint de prueba funciona: `/wp-json/placas/v1/test`
- [ ] Email configurado en WordPress Admin
- [ ] Post es nuevo (no existía antes)
- [ ] Post tiene título y contenido
- [ ] Revisada carpeta de spam
- [ ] WordPress puede enviar emails (probar con plugin SMTP)
- [ ] Logs de WordPress revisados (si WP_DEBUG activo)

---

## 🎯 Próximos Pasos

1. **Prueba el endpoint:** `saltanews.com.ar/wp-json/placas/v1/test`
2. **Si el email de prueba no se envía:** Instala un plugin SMTP
3. **Crea un post nuevo** y publícalo
4. **Revisa** el email y spam

---

## 📞 Si Nada Funciona

1. **Revisa** los logs del servidor
2. **Contacta** a tu proveedor de hosting para verificar:
   - Si `mail()` de PHP está habilitado
   - Si hay restricciones de envío de email
3. **Usa** un plugin SMTP profesional

---

**El plugin actualizado ahora tiene:**
- ✅ Logs de debug
- ✅ Mejor manejo de errores
- ✅ Endpoint de prueba
- ✅ Verificación de email configurado
