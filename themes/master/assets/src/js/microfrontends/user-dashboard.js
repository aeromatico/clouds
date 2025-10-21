/**
 * Microfrontend: User Dashboard
 * Ejemplo de uso del API Manager con cache compartido
 */

import { createApiComponent, apiUtils } from '../core/alpine-api-mixin.js';

document.addEventListener('alpine:init', () => {
    Alpine.data('userDashboard', createApiComponent({
        // Estado específico del componente
        user: null,
        stats: null,
        notifications: [],
        
        // Configuración
        refreshInterval: 60000, // 1 minuto
        
        async init() {
            console.log('[UserDashboard] Inicializando...');
            
            // Cargar datos iniciales
            await this.loadDashboardData();
            
            // Suscribirse a cambios en datos de usuario
            this.apiSubscribe('/api/user/profile', (userData) => {
                console.log('[UserDashboard] Usuario actualizado:', userData);
                this.user = userData;
            });
            
            // Polling para notificaciones
            this.startNotificationPolling();
        },

        /**
         * Carga todos los datos del dashboard
         */
        async loadDashboardData() {
            try {
                // Batch fetch de múltiples endpoints relacionados
                const results = await apiUtils.batchFetch([
                    '/api/user/profile',
                    '/api/user/stats',
                    '/api/user/notifications'
                ], {
                    cache: true,
                    ttl: 5 * 60 * 1000 // 5 minutos de cache
                });

                // Procesar resultados
                if (results[0].status === 'fulfilled') {
                    this.user = results[0].value;
                }
                
                if (results[1].status === 'fulfilled') {
                    this.stats = results[1].value;
                }
                
                if (results[2].status === 'fulfilled') {
                    this.notifications = results[2].value;
                }

                console.log('[UserDashboard] Datos cargados desde cache');
                
            } catch (error) {
                console.error('[UserDashboard] Error cargando datos:', error);
            }
        },

        /**
         * Actualizar perfil de usuario
         */
        async updateProfile(profileData) {
            try {
                const result = await this.apiCall('/api/user/profile', {
                    method: 'PUT',
                    body: JSON.stringify(profileData),
                    cache: false // No cache para operaciones de escritura
                });

                this.user = result;
                
                // El API Manager automáticamente invalidará el cache relacionado
                console.log('[UserDashboard] Perfil actualizado');
                
                return result;
            } catch (error) {
                console.error('[UserDashboard] Error actualizando perfil:', error);
                throw error;
            }
        },

        /**
         * Marcar notificación como leída
         */
        async markNotificationRead(notificationId) {
            try {
                await this.apiCall(`/api/user/notifications/${notificationId}/read`, {
                    method: 'POST',
                    cache: false
                });

                // Actualizar estado local
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.read = true;
                }

                console.log('[UserDashboard] Notificación marcada como leída');
                
            } catch (error) {
                console.error('[UserDashboard] Error marcando notificación:', error);
            }
        },

        /**
         * Polling para notificaciones nuevas
         */
        startNotificationPolling() {
            return apiUtils.startPolling('/api/user/notifications', this.refreshInterval, {
                cache: true,
                ttl: 30000 // 30 segundos para notificaciones
            });
        },

        /**
         * Refrescar todos los datos manualmente
         */
        async refresh() {
            // Invalidar cache y recargar
            this.apiInvalidate('/api/user/');
            await this.loadDashboardData();
        },

        /**
         * Getters computados
         */
        get unreadNotifications() {
            return this.notifications?.filter(n => !n.read) || [];
        },

        get userInitials() {
            if (!this.user?.name) return '';
            return this.user.name.split(' ')
                .map(word => word[0])
                .join('')
                .toUpperCase();
        }
    }));
});