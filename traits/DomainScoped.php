<?php namespace Aero\Clouds\Traits;

use Aero\Clouds\Classes\DomainHelper;
use Illuminate\Database\Eloquent\Builder;

/**
 * DomainScoped Trait
 *
 * Implementa filtrado automático por dominio en todos los modelos.
 * - Auto-asigna el dominio actual al crear registros
 * - Filtra automáticamente queries por el dominio actual
 * - Puede deshabilitarse temporalmente si es necesario
 *
 * Uso en modelo:
 * ```php
 * class Order extends Model
 * {
 *     use \Aero\Clouds\Traits\DomainScoped;
 * }
 * ```
 *
 * Deshabilitar temporalmente el scope:
 * ```php
 * Order::withoutDomainScope()->get();  // Todos los dominios
 * Order::forDomain('otro.com')->get();  // Dominio específico
 * ```
 */
trait DomainScoped
{
    /**
     * Boot del trait - se ejecuta cuando el modelo se inicializa
     */
    protected static function bootDomainScoped()
    {
        // Agregar Global Scope para filtrar automáticamente por dominio
        static::addGlobalScope('domain', function (Builder $builder) {
            $builder->where(static::getDomainColumn(), DomainHelper::current());
        });

        // Al crear un nuevo registro, auto-asignar el dominio
        static::creating(function ($model) {
            if (!$model->{static::getDomainColumn()}) {
                $model->{static::getDomainColumn()} = DomainHelper::current();
            }
        });
    }

    /**
     * Scope para queries sin filtro de dominio (ver todos los dominios)
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithoutDomainScope($query)
    {
        return $query->withoutGlobalScope('domain');
    }

    /**
     * Scope para queries de un dominio específico
     *
     * @param Builder $query
     * @param string $domain
     * @return Builder
     */
    public function scopeForDomain($query, $domain)
    {
        return $query->withoutGlobalScope('domain')
                     ->where(static::getDomainColumn(), DomainHelper::clean($domain));
    }

    /**
     * Scope para queries de múltiples dominios
     *
     * @param Builder $query
     * @param array $domains
     * @return Builder
     */
    public function scopeForDomains($query, array $domains)
    {
        $cleaned = array_map([DomainHelper::class, 'clean'], $domains);

        return $query->withoutGlobalScope('domain')
                     ->whereIn(static::getDomainColumn(), $cleaned);
    }

    /**
     * Obtener el nombre de la columna de dominio
     *
     * @return string
     */
    public static function getDomainColumn()
    {
        return 'domain';
    }

    /**
     * Obtener el dominio del registro actual
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->{static::getDomainColumn()};
    }

    /**
     * Verificar si el registro pertenece al dominio actual
     *
     * @return bool
     */
    public function isFromCurrentDomain()
    {
        return $this->getDomain() === DomainHelper::current();
    }

    /**
     * Verificar si el registro pertenece a un dominio específico
     *
     * @param string $domain
     * @return bool
     */
    public function isFromDomain($domain)
    {
        return $this->getDomain() === DomainHelper::clean($domain);
    }
}
