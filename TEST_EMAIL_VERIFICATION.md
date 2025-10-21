# Guía Rápida - Probar Verificación de Email

## 🚀 Método 1: Usando el Comando Artisan

### Probar con un usuario existente:
```bash
php artisan aero:test-email-verification wmrme5865@gmail.com
```

### Crear un nuevo usuario de prueba:
```bash
php artisan aero:test-email-verification tu-email@gmail.com --create
```

### El comando te mostrará:
- ✅ Si el usuario existe o se creó
- 📊 Estado de verificación actual
- 📧 Confirmación de envío
- 📋 Instrucciones para verificar

---

## 🎯 Método 2: Usando el Script Helper

### Probar rápidamente:
```bash
./test-email-verification.sh tu-email@gmail.com
```

### Crear usuario y enviar:
```bash
./test-email-verification.sh tu-email@gmail.com --create
```

---

## 👥 Usuarios Disponibles para Probar

Según la base de datos, estos usuarios **NO** tienen email verificado:

1. **wmrme5865@gmail.com** (Katerine Colque)
2. **kttylia931alba@gmail.com** (katerine colque)

Usuarios con email **YA VERIFICADO**:

1. **aeromatico@gmail.com** (Álvaro Salcedo) ✅
2. **arnold@5megas.com** (Arnold Copa) ✅

---

## 📧 Ejemplo de Prueba Completa

### 1. Enviar correo de verificación a usuario existente:
```bash
php artisan aero:test-email-verification wmrme5865@gmail.com
```

**Salida esperada:**
```
🔍 Buscando usuario con email: wmrme5865@gmail.com
✅ Usuario encontrado: Katerine Colque (ID: 3)
ℹ️  El usuario NO tiene el email verificado

📧 Preparando envío de correo de verificación...

✅ ¡Correo de verificación enviado exitosamente!

┌─────────────┬───────────────────────────────┐
│ Campo       │ Valor                         │
├─────────────┼───────────────────────────────┤
│ Destinatario│ wmrme5865@gmail.com           │
│ Nombre      │ Katerine Colque               │
│ Template    │ user:verify_email             │
│ URL Base    │ https://clouds.com.bo/verify-email │
│ Código generado│ ✓                          │
└─────────────┴───────────────────────────────┘

📋 Próximos pasos:
1. Revisa la bandeja de entrada de: wmrme5865@gmail.com
2. Busca el email con asunto: "Confirm your email address"
3. Haz clic en el botón "Confirm Email Address"

📊 Para ver logs:
   tail -f storage/logs/october.log

🖥️  Ver en backend:
   Cloud Commerce → Email Logs
```

### 2. Crear un usuario de prueba nuevo:
```bash
php artisan aero:test-email-verification test@clouds.com.bo --create
```

**Salida esperada:**
```
🔍 Buscando usuario con email: test@clouds.com.bo
👤 Creando usuario de prueba...
✅ Usuario creado con ID: 5
ℹ️  El usuario NO tiene el email verificado

📧 Preparando envío de correo de verificación...

✅ ¡Correo de verificación enviado exitosamente!
...
```

---

## 🔍 Verificar que Funciona

### Opción A: Revisar Logs (si MAIL_MAILER=log)
```bash
tail -f storage/logs/october.log | grep -A 50 "verify_email"
```

### Opción B: Revisar Base de Datos
```bash
mysql -u master -pTMeeWx0F7YDUqsN16nDl master -e \
  "SELECT * FROM aero_clouds_email_logs WHERE template_code = 'user:verify_email' ORDER BY created_at DESC LIMIT 5"
```

### Opción C: Backend (Email Logs)
1. Ir a: **Cloud Commerce → Email Logs**
2. Filtrar por template: `user:verify_email`
3. Ver estado: `sent` o `failed`

---

## ⚠️ Importante

### Si MAIL_MAILER=log:
- Los correos **NO se envían** realmente
- Se guardan en `storage/logs/october.log`
- Útil para testing sin enviar emails reales

### Si MAIL_MAILER=smtp:
- Los correos **SE ENVÍAN** a la bandeja real
- Requiere credenciales SMTP válidas
- Perfecto para pruebas en producción

---

## 🎓 Ejemplos de Uso

### Probar con Gmail personal:
```bash
php artisan aero:test-email-verification tu-email@gmail.com --create
```

### Probar con usuario existente:
```bash
php artisan aero:test-email-verification wmrme5865@gmail.com
```

### Probar reenvío (usuario ya verificado):
```bash
php artisan aero:test-email-verification aeromatico@gmail.com
```
El comando preguntará si deseas enviar de todas formas.

---

## 🐛 Troubleshooting

### Error: Usuario no encontrado
```bash
# Solución: Crear el usuario
php artisan aero:test-email-verification email@example.com --create
```

### Error: Email inválido
```bash
# Verificar formato del email
php artisan aero:test-email-verification "test@example.com"
```

### No recibo el correo
1. Verificar configuración SMTP en `.env`
2. Revisar logs: `tail -f storage/logs/october.log`
3. Verificar spam en bandeja de entrada
4. Revisar Email Logs en backend

---

**Listo para probar!** 🚀
