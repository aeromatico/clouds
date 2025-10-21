#!/bin/bash

# Script para probar fácilmente el envío de correos de verificación
# Uso: ./test-email-verification.sh tu-email@example.com

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║       Test de Verificación de Email - Clouds.com.bo         ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Verificar si se proporcionó un email
if [ -z "$1" ]; then
    echo "❌ Error: Debes proporcionar un email"
    echo ""
    echo "Uso:"
    echo "  ./test-email-verification.sh tu-email@example.com"
    echo ""
    echo "Opciones:"
    echo "  ./test-email-verification.sh tu-email@example.com --create  # Crear usuario si no existe"
    echo ""
    exit 1
fi

EMAIL="$1"
CREATE_FLAG="$2"

echo "📧 Email de prueba: $EMAIL"
echo ""

# Verificar configuración SMTP
echo "🔍 Verificando configuración SMTP..."
MAILER=$(grep "^MAIL_MAILER=" .env | cut -d'=' -f2)
MAIL_HOST=$(grep "^MAIL_HOST=" .env | cut -d'=' -f2)

if [ "$MAILER" = "log" ]; then
    echo "⚠️  ADVERTENCIA: MAIL_MAILER está en modo 'log'"
    echo "   Los correos NO se enviarán realmente, solo se guardarán en logs"
    echo "   Para enviar correos reales, cambia MAIL_MAILER=smtp en .env"
    echo ""
fi

if [ "$MAILER" = "smtp" ]; then
    echo "✅ SMTP configurado: $MAIL_HOST"
    echo ""
fi

# Ejecutar el comando
echo "🚀 Ejecutando comando de prueba..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ "$CREATE_FLAG" = "--create" ]; then
    php artisan aero:test-email-verification "$EMAIL" --create
else
    php artisan aero:test-email-verification "$EMAIL"
fi

RESULT=$?

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $RESULT -eq 0 ]; then
    echo ""
    echo "✅ Comando ejecutado exitosamente"
    echo ""

    if [ "$MAILER" = "log" ]; then
        echo "📋 Para ver el correo en logs:"
        echo "   tail -f storage/logs/october.log"
    else
        echo "📬 Revisa la bandeja de entrada de: $EMAIL"
    fi

    echo ""
    echo "🖥️  Ver en backend:"
    echo "   Cloud Commerce → Email Logs"
    echo ""
else
    echo ""
    echo "❌ Hubo un error al ejecutar el comando"
    echo ""
fi

exit $RESULT
