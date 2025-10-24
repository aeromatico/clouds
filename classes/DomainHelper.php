<?php namespace Aero\Clouds\Classes;

use Request;

/**
 * DomainHelper
 *
 * Helper centralizado para obtener el dominio actual sin www, http, https.
 * Usado por el sistema multi-dominio del plugin.
 *
 * Uso:
 * ```php
 * $domain = DomainHelper::current();  // 'boliviahost.com'
 * ```
 */
class DomainHelper
{
    /**
     * Dominio por defecto del sistema
     */
    const DEFAULT_DOMAIN = 'clouds.com.bo';

    /**
     * Cache del dominio actual en la request
     * @var string|null
     */
    protected static $currentDomain = null;

    /**
     * Obtener el dominio actual limpio (sin www, http, https, puertos)
     *
     * @return string
     */
    public static function current()
    {
        // Si ya lo calculamos en esta request, retornarlo
        if (self::$currentDomain !== null) {
            return self::$currentDomain;
        }

        // Obtener el host de la request
        $host = Request::getHttpHost();

        // Si no hay request (ej: CLI), usar el default
        if (!$host) {
            self::$currentDomain = self::DEFAULT_DOMAIN;
            return self::$currentDomain;
        }

        // Limpiar el dominio
        self::$currentDomain = self::clean($host);

        return self::$currentDomain;
    }

    /**
     * Limpiar un dominio (remover www, puertos, etc.)
     *
     * @param string $domain
     * @return string
     */
    public static function clean($domain)
    {
        if (empty($domain)) {
            return self::DEFAULT_DOMAIN;
        }

        // Remover www.
        $domain = preg_replace('/^www\./i', '', $domain);

        // Remover puerto si existe
        $domain = preg_replace('/:\d+$/', '', $domain);

        // Convertir a minúsculas
        $domain = strtolower(trim($domain));

        // Si quedó vacío, usar default
        if (empty($domain)) {
            return self::DEFAULT_DOMAIN;
        }

        return $domain;
    }

    /**
     * Verificar si un dominio es válido
     *
     * @param string $domain
     * @return bool
     */
    public static function isValid($domain)
    {
        if (empty($domain)) {
            return false;
        }

        // Validar formato de dominio
        return preg_match('/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/i', $domain);
    }

    /**
     * Obtener el dominio default del sistema
     *
     * @return string
     */
    public static function default()
    {
        return self::DEFAULT_DOMAIN;
    }

    /**
     * Resetear el cache del dominio actual (útil para testing)
     */
    public static function reset()
    {
        self::$currentDomain = null;
    }

    /**
     * Forzar un dominio específico (útil para testing o CLI)
     *
     * @param string $domain
     */
    public static function setCurrentDomain($domain)
    {
        self::$currentDomain = self::clean($domain);
    }
}
