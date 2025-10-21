/**
 * API Manager - Sistema centralizado de gestión de APIs con cache
 * Permite reutilizar requests entre microfrontends
 */

class ApiManager {
    constructor() {
        this.cache = new Map();
        this.pendingRequests = new Map();
        this.defaultTTL = 5 * 60 * 1000; // 5 minutos por defecto
        this.subscribers = new Map(); // Para notificaciones en tiempo real
    }

    /**
     * Realiza un request con cache automático
     * @param {string} endpoint - URL del endpoint
     * @param {object} options - Opciones de fetch + cache
     * @returns {Promise}
     */
    async fetch(endpoint, options = {}) {
        const {
            method = 'GET',
            cache: enableCache = true,
            ttl = this.defaultTTL,
            forceRefresh = false,
            ...fetchOptions
        } = options;

        const cacheKey = this.generateCacheKey(endpoint, method, fetchOptions);

        // Si no queremos cache o es un método que modifica datos
        if (!enableCache || !['GET', 'HEAD'].includes(method)) {
            const result = await this.performRequest(endpoint, { method, ...fetchOptions });
            
            // Si es un método que modifica datos, invalidar cache relacionado
            if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
                this.invalidateRelated(endpoint);
            }
            
            return result;
        }

        // Verificar cache existente
        if (!forceRefresh && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() < cached.expiry) {
                console.log(`[API Manager] Cache hit: ${endpoint}`);
                return cached.data;
            } else {
                this.cache.delete(cacheKey);
            }
        }

        // Si ya hay un request pendiente para este endpoint, reutilizarlo
        if (this.pendingRequests.has(cacheKey)) {
            console.log(`[API Manager] Reusing pending request: ${endpoint}`);
            return this.pendingRequests.get(cacheKey);
        }

        // Realizar nuevo request
        const requestPromise = this.performRequest(endpoint, { method, ...fetchOptions });
        this.pendingRequests.set(cacheKey, requestPromise);

        try {
            const result = await requestPromise;
            
            // Guardar en cache
            this.cache.set(cacheKey, {
                data: result,
                expiry: Date.now() + ttl,
                endpoint,
                timestamp: Date.now()
            });

            // Notificar a suscriptores
            this.notifySubscribers(endpoint, result);

            console.log(`[API Manager] Cache stored: ${endpoint}`);
            return result;
        } finally {
            this.pendingRequests.delete(cacheKey);
        }
    }

    /**
     * Realiza el request HTTP real
     */
    async performRequest(endpoint, options) {
        try {
            const response = await fetch(endpoint, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers
                },
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
        } catch (error) {
            console.error(`[API Manager] Request failed: ${endpoint}`, error);
            throw error;
        }
    }

    /**
     * Genera clave única para cache
     */
    generateCacheKey(endpoint, method, options) {
        const sortedOptions = JSON.stringify(options, Object.keys(options).sort());
        return `${method}:${endpoint}:${btoa(sortedOptions)}`;
    }

    /**
     * Invalida cache relacionado con un endpoint
     */
    invalidateRelated(endpoint) {
        const baseEndpoint = endpoint.split('?')[0]; // Remover query params
        
        for (const [key, cached] of this.cache.entries()) {
            if (cached.endpoint.startsWith(baseEndpoint)) {
                this.cache.delete(key);
                console.log(`[API Manager] Cache invalidated: ${cached.endpoint}`);
            }
        }
    }

    /**
     * Suscribirse a cambios en un endpoint específico
     */
    subscribe(endpoint, callback) {
        if (!this.subscribers.has(endpoint)) {
            this.subscribers.set(endpoint, new Set());
        }
        this.subscribers.get(endpoint).add(callback);

        // Retornar función para desuscribirse
        return () => {
            const callbacks = this.subscribers.get(endpoint);
            if (callbacks) {
                callbacks.delete(callback);
                if (callbacks.size === 0) {
                    this.subscribers.delete(endpoint);
                }
            }
        };
    }

    /**
     * Notificar a suscriptores sobre cambios
     */
    notifySubscribers(endpoint, data) {
        const callbacks = this.subscribers.get(endpoint);
        if (callbacks) {
            callbacks.forEach(callback => {
                try {
                    callback(data, endpoint);
                } catch (error) {
                    console.error('[API Manager] Subscriber error:', error);
                }
            });
        }
    }

    /**
     * Limpiar cache manualmente
     */
    clearCache(pattern = null) {
        if (!pattern) {
            this.cache.clear();
            console.log('[API Manager] All cache cleared');
            return;
        }

        for (const [key, cached] of this.cache.entries()) {
            if (cached.endpoint.includes(pattern)) {
                this.cache.delete(key);
                console.log(`[API Manager] Cache cleared: ${cached.endpoint}`);
            }
        }
    }

    /**
     * Obtener estadísticas del cache
     */
    getCacheStats() {
        const stats = {
            total: this.cache.size,
            pending: this.pendingRequests.size,
            subscribers: this.subscribers.size,
            entries: []
        };

        for (const [key, cached] of this.cache.entries()) {
            stats.entries.push({
                endpoint: cached.endpoint,
                age: Date.now() - cached.timestamp,
                expires: cached.expiry - Date.now()
            });
        }

        return stats;
    }

    /**
     * Prefetch de datos (útil para precargar)
     */
    async prefetch(endpoints, options = {}) {
        const promises = endpoints.map(endpoint => 
            this.fetch(endpoint, { ...options, cache: true })
                .catch(error => console.warn(`[API Manager] Prefetch failed: ${endpoint}`, error))
        );
        
        await Promise.allSettled(promises);
        console.log(`[API Manager] Prefetched ${endpoints.length} endpoints`);
    }
}

// Crear instancia global
window.apiManager = new ApiManager();

// Exportar para módulos
export default window.apiManager;