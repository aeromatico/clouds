# Sistema de Verificación de Email - Clouds.com.bo

Sistema completo de verificación de correo electrónico para nuevos usuarios registrados.

## 🎯 Cómo Funciona

### 1. **Registro de Usuario**
Cuando un usuario se registra en `/register`:
- Se crea el usuario en la base de datos
- Se dispara el evento `rainlab.user.register`
- Se envía automáticamente un correo de verificación

### 2. **Correo de Verificación**
El usuario recibe un email con:
- **Asunto:** "Confirm your email address"
- **Template:** `user:verify_email`
- **Contenido:** Enlace para verificar el email
- **Enlace:** `https://clouds.com.bo/verify-email?verify=CODIGO`
- **Expiración:** 60 minutos (configurable)

### 3. **Verificación**
Cuando el usuario hace clic en el enlace:
- Se valida el código de verificación
- Se marca el usuario como verificado (`activated_at`)
- Se envía un correo de bienvenida (si está habilitado)
- Se crea un log de actividad

### 4. **Correo de Bienvenida** (Opcional)
Después de verificar el email:
- **Template:** `user:welcome_email`
- Se envía solo si `notify_user = true`
- Configurable desde el backend

## 📁 Archivos Importantes

### Backend
- **Plugin.php** (`plugins/aero/clouds/Plugin.php`)
  - Línea 199-210: Event listener para envío de verificación

### Frontend
- **Página de registro:** `themes/master/pages/register.htm`
- **Página de verificación:** `themes/master/pages/verify-email.htm`

### Plantillas de Correo
- **Verificación:** `plugins/rainlab/user/views/mail/verify_email.htm`
- **Bienvenida:** `plugins/rainlab/user/views/mail/welcome_email.htm`

## ⚙️ Configuración

### Variables de Entorno (.env)

```bash
# SMTP Configuration (requerido para envío de correos)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@clouds.com.bo
MAIL_FROM_NAME="Clouds Hosting"
```

### Configuración en Backend

1. Ve a: **Settings → Users → User Settings**
2. Tab "Notifications":
   - ✅ **Notify User:** Activado
   - **User Message Template:** `user:welcome_email`
   - ⬜ **Notify Administrators:** (opcional)

3. Tab "Registration":
   - ✅ **Allow User Registration:** Activado
   - **Password Length:** 8
   - ✅ **Require Number:** Activado

## 🔄 Flujo Completo

```
┌─────────────────┐
│ Usuario se      │
│ registra en     │
│ /register       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Se crea usuario │
│ (no verificado) │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Evento:         │
│ rainlab.user.   │
│ register        │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Envío de correo │
│ de verificación │
│ user:verify_    │
│ email           │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Usuario recibe  │
│ email con link  │
│ /verify-email?  │
│ verify=CODE     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Usuario hace    │
│ clic en link    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Validación del  │
│ código          │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Usuario marcado │
│ como verificado │
│ (activated_at)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Envío de correo │
│ de bienvenida   │
│ user:welcome_   │
│ email (opcional)│
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Redirigir a     │
│ /dashboard      │
└─────────────────┘
```

## 🧪 Cómo Probar

### 1. **Configurar SMTP**
```bash
# Editar .env
nano .env

# Cambiar MAIL_MAILER de "log" a "smtp"
MAIL_MAILER=smtp

# Configurar credenciales SMTP
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
```

### 2. **Limpiar Cache**
```bash
php artisan cache:clear
```

### 3. **Registrar un Usuario**
1. Ir a: `https://clouds.com.bo/register`
2. Llenar formulario:
   - Email: tu-email@gmail.com
   - Nombre: Test
   - Apellido: User
   - Contraseña: Test1234
   - Confirmar: Test1234
3. Hacer clic en "Crear cuenta"

### 4. **Verificar Logs**
```bash
# Ver logs de la aplicación
tail -f storage/logs/october.log

# Buscar línea:
# Email verification sent to: tu-email@gmail.com
```

### 5. **Revisar Email**
- Abrir bandeja de entrada
- Buscar email con asunto "Confirm your email address"
- Hacer clic en el botón "Confirm Email Address"

### 6. **Verificar Dashboard**
- Después de verificar, deberías ser redirigido a `/dashboard`
- Revisar si hay mensaje de éxito

## 🔍 Troubleshooting

### No recibo el correo de verificación

**Verificar configuración SMTP:**
```bash
# Probar conexión SMTP
telnet smtp.gmail.com 587

# Ver logs
tail -f storage/logs/october.log
```

**Verificar cola (si está habilitada):**
```bash
# Ver cola de Redis
redis-cli -n 3
LLEN queues:default

# Procesar cola manualmente
php artisan queue:work --once
```

**Verificar que el correo se envió:**
```bash
# Buscar en logs
grep "Email verification sent" storage/logs/october.log

# Verificar en base de datos
mysql -u master -pTMeeWx0F7YDUqsN16nDl master -e \
  "SELECT * FROM aero_clouds_email_logs ORDER BY created_at DESC LIMIT 5"
```

### El link de verificación no funciona

**Verificar código:**
```sql
-- Revisar código de activación del usuario
SELECT id, email, activation_code, activated_at
FROM users
WHERE email = 'tu-email@gmail.com';
```

**Verificar que la página existe:**
```bash
# Debe existir
ls -la themes/master/pages/verify-email.htm
```

### El código expiró

**Reenviar correo de verificación:**
1. Iniciar sesión con el usuario
2. Ir a: `https://clouds.com.bo/verify-email`
3. Hacer clic en "Reenviar Email de Verificación"

## 📊 Monitoreo

### Ver emails enviados en el Backend

1. Ir a: **Cloud Commerce → Email Logs**
2. Filtrar por template: `user:verify_email`
3. Ver estado: sent, failed, queued

### Consultar desde código

```php
use Aero\Clouds\Models\EmailLog;

// Últimos correos de verificación
$verificationEmails = EmailLog::template('user:verify_email')
    ->lastDays(7)
    ->get();

// Correos de verificación fallidos
$failed = EmailLog::template('user:verify_email')
    ->failed()
    ->get();
```

## 🎨 Personalizar Plantillas

### Editar plantilla de verificación

1. Backend → **Settings → Mail → Mail Templates**
2. Buscar: `user:verify_email`
3. Editar:
   - Subject
   - HTML content
   - Text content

### Editar plantilla de bienvenida

1. Backend → **Settings → Mail → Mail Templates**
2. Buscar: `user:welcome_email`
3. Personalizar contenido

## 🔐 Seguridad

- **Expiración:** Los códigos expiran en 60 minutos
- **Un solo uso:** Cada código solo puede usarse una vez
- **Rate limiting:** Límite de intentos de verificación
- **Validación:** Código incluye timestamp, user_id y token aleatorio

## 📝 Notas Importantes

1. **SMTP es requerido:** En producción, configura un servidor SMTP real
2. **Verificación no obligatoria:** Los usuarios pueden acceder al dashboard sin verificar (por ahora)
3. **Logs:** Todos los correos se registran en `aero_clouds_email_logs`
4. **Eventos:** Sistema usa eventos de Laravel/October para flexibilidad

## 🚀 Próximas Mejoras

- [ ] Hacer verificación obligatoria (bloquear acceso sin verificar)
- [ ] Agregar verificación por SMS como alternativa
- [ ] Dashboard de estadísticas de verificación
- [ ] Recordatorios automáticos para usuarios no verificados
- [ ] Integración con el sistema de bienvenida/onboarding

---

**Última actualización:** 2025-01-06
**Versión:** 1.0.0
