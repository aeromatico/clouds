/**
 * Alpine.js API Mixin - Facilita el uso del API Manager en microfrontends
 */

// Mixin base para componentes que usan APIs
export const apiMixin = {
    // Estado base para loading y errores
    loading: false,
    error: null,
    lastUpdate: null,
    
    // Suscripciones activas (se limpian automáticamente)
    _subscriptions: [],

    /**
     * Wrapper del API Manager con manejo automático de loading/error
     * @param {string} endpoint 
     * @param {object} options 
     * @returns {Promise}
     */
    async apiCall(endpoint, options = {}) {
        this.loading = true;
        this.error = null;
        
        try {
            const result = await window.apiManager.fetch(endpoint, options);
            this.lastUpdate = new Date().toISOString();
            return result;
        } catch (error) {
            this.error = error.message;
            console.error(`[${this.$el?.dataset?.component || 'Unknown'}] API Error:`, error);
            throw error;
        } finally {
            this.loading = false;
        }
    },

    /**
     * Suscribirse a cambios en un endpoint
     * @param {string} endpoint 
     * @param {function} callback 
     */
    apiSubscribe(endpoint, callback) {
        const unsubscribe = window.apiManager.subscribe(endpoint, callback);
        this._subscriptions.push(unsubscribe);
        return unsubscribe;
    },

    /**
     * Invalidar cache de endpoint específico
     * @param {string} pattern 
     */
    apiInvalidate(pattern) {
        window.apiManager.clearCache(pattern);
    },

    /**
     * Limpiar suscripciones al destruir el componente
     */
    apiCleanup() {
        this._subscriptions.forEach(unsubscribe => unsubscribe());
        this._subscriptions = [];
    },

    /**
     * Método de inicialización base
     */
    apiInit() {
        // Limpiar suscripciones cuando el elemento se remueva del DOM
        if (this.$el) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.removedNodes.forEach((node) => {
                        if (node === this.$el || node.contains?.(this.$el)) {
                            this.apiCleanup();
                            observer.disconnect();
                        }
                    });
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }
};

/**
 * Factory para crear componentes con API integrado
 * @param {object} componentData - Datos específicos del componente
 * @returns {function} - Función de componente Alpine
 */
export function createApiComponent(componentData = {}) {
    return () => ({
        ...apiMixin,
        ...componentData,
        
        // Override init para llamar apiInit automáticamente
        init() {
            this.apiInit();
            
            // Llamar init personalizado si existe
            if (componentData.init && typeof componentData.init === 'function') {
                return componentData.init.call(this);
            }
        }
    });
}

/**
 * Utilidades globales para APIs
 */
export const apiUtils = {
    /**
     * Formato estándar de endpoints con parámetros
     */
    buildEndpoint(base, params = {}) {
        const url = new URL(base, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                url.searchParams.set(key, value);
            }
        });
        return url.toString();
    },

    /**
     * Batch de requests con cache compartido
     */
    async batchFetch(endpoints, options = {}) {
        const promises = endpoints.map(endpoint => 
            window.apiManager.fetch(endpoint, options)
        );
        return Promise.allSettled(promises);
    },

    /**
     * Polling de endpoint con cache inteligente
     */
    startPolling(endpoint, interval = 30000, options = {}) {
        let timeoutId;
        
        const poll = async () => {
            try {
                await window.apiManager.fetch(endpoint, {
                    ...options,
                    forceRefresh: true
                });
            } catch (error) {
                console.warn(`[API Utils] Polling error: ${endpoint}`, error);
            }
            
            timeoutId = setTimeout(poll, interval);
        };

        // Iniciar polling
        poll();

        // Retornar función para detener
        return () => {
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
        };
    }
};

// Registrar utilidades globalmente
window.apiUtils = apiUtils;