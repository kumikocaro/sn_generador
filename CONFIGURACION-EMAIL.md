# 📧 Configuración del Email - Auto Placas Instagram

## 📍 ¿A dónde se envía el email?

Por **defecto**, el email se envía al **email del administrador de WordPress**.

---

## ✅ Cómo Verificar el Email Actual

### Método 1: Desde WordPress Admin

1. **Inicia sesión** en WordPress Admin
2. **Ve a:** Configuración → General
3. **Busca:** "Dirección de correo electrónico"
4. **Ese es el email** donde se enviarán las notificaciones

### Método 2: Desde el Código

El plugin usa esta línea:
```php
$admin_email = get_option('admin_email');
```

Este valor se toma de la configuración de WordPress.

---

## 🔧 Cómo Cambiar el Email de Destino

Tienes **3 opciones** para configurar el email:

### Opción 1: Cambiar el Email del Administrador (Recomendado)

**Ventaja:** Afecta a todas las notificaciones de WordPress

1. **Ve a:** WordPress Admin → Configuración → General
2. **Cambia:** "Dirección de correo electrónico"
3. **Guarda** los cambios

✅ **Ventaja:** Todos los emails de WordPress irán a este nuevo email

---

### Opción 2: Email Específico en el Plugin

**Ventaja:** Solo afecta a las notificaciones de placas

1. **Abre el archivo:** `sn-auto-placas.php`
2. **Busca la línea ~30:**
```php
$admin_email = get_option('admin_email');
```
3. **Comenta esa línea** y **descomenta** esta:
```php
// $admin_email = get_option('admin_email');
$admin_email = 'tu-email@ejemplo.com';
```
4. **Reemplaza** `tu-email@ejemplo.com` con tu email real
5. **Sube el archivo** actualizado al servidor

**Ejemplo:**
```php
// $admin_email = get_option('admin_email');
$admin_email = 'redaccion@saltanews.com.ar';
```

---

### Opción 3: Múltiples Emails

**Ventaja:** Envía a varios emails a la vez

1. **Abre el archivo:** `sn-auto-placas.php`
2. **Busca la línea ~30:**
```php
$admin_email = get_option('admin_email');
```
3. **Reemplázala por:**
```php
$admin_email = array(
    'email1@saltanews.com.ar',
    'email2@saltanews.com.ar',
    'redaccion@saltanews.com.ar'
);
```

✅ **Ventaja:** Todos los emails en el array recibirán la notificación

---

## 🧪 Cómo Probar que el Email Funciona

### Paso 1: Verificar Configuración

1. **Verifica** el email en WordPress Admin → Configuración → General
2. **O verifica** el email en el código del plugin

### Paso 2: Hacer Prueba

1. **Crea un post de prueba** en WordPress
2. **Asegúrate** de que tenga:
   - ✅ Título
   - ✅ Contenido
   - ✅ Imagen destacada (recomendado)
3. **Publica el post**
4. **Revisa el email** configurado
5. **Revisa también la carpeta de spam**

### Paso 3: Verificar que Llegó

Deberías recibir un email con:
- ✅ Asunto: "📱 Nueva placa lista para Instagram - [Título del post]"
- ✅ Botón verde: "📱 Abrir y Compartir en Instagram"
- ✅ Información del post

---

## ⚠️ Si el Email No Llega

### Problema 1: WordPress no puede enviar emails

**Solución:**
1. **Instala un plugin de email** como "WP Mail SMTP"
2. **Configura** SMTP con tu proveedor de email
3. **Prueba** el envío de emails

### Problema 2: Email en carpeta de spam

**Solución:**
1. **Revisa** la carpeta de spam/correo no deseado
2. **Marca** el email como "No es spam"
3. **Agrega** el remitente a contactos

### Problema 3: Email incorrecto

**Solución:**
1. **Verifica** el email en WordPress Admin
2. **Verifica** el email en el código del plugin
3. **Asegúrate** de que el email esté bien escrito

---

## 📋 Resumen Rápido

| Método | Dónde Configurar | Ventaja |
|--------|------------------|---------|
| **Opción 1** | WordPress Admin → Configuración → General | Afecta a todo WordPress |
| **Opción 2** | Archivo `sn-auto-placas.php` línea ~30 | Solo afecta a placas |
| **Opción 3** | Archivo `sn-auto-placas.php` línea ~30 | Múltiples destinatarios |

---

## ✅ Checklist

- [ ] Verificado el email actual en WordPress Admin
- [ ] Decidido qué método usar (1, 2 o 3)
- [ ] Configurado el email según el método elegido
- [ ] Probado con un post de prueba
- [ ] Verificado que el email llegó correctamente
- [ ] Revisada la carpeta de spam si no llegó

---

## 🎯 Recomendación

**Para uso personal:** Usa la **Opción 1** (cambiar email del admin)

**Para uso en equipo:** Usa la **Opción 3** (múltiples emails)

**Para email específico solo para placas:** Usa la **Opción 2**

---

**¿Necesitas ayuda?** Revisa los logs de WordPress o contacta a tu proveedor de hosting para verificar la configuración de email del servidor.
