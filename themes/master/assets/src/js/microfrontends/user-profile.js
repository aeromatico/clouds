/**
 * Microfrontend: User Profile
 * Otro componente que reutiliza el mismo endpoint /api/user/profile
 */

import { createApiComponent } from '../core/alpine-api-mixin.js';

document.addEventListener('alpine:init', () => {
    Alpine.data('userProfile', createApiComponent({
        // Estado del componente
        user: null,
        isEditing: false,
        editForm: {
            name: '',
            email: '',
            bio: ''
        },

        async init() {
            console.log('[UserProfile] Inicializando...');
            
            // Cargar perfil - esto reutilizará el cache del user-dashboard
            // si ya fue cargado anteriormente
            await this.loadProfile();
            
            // Suscribirse a cambios (por ejemplo, desde user-dashboard)
            this.apiSubscribe('/api/user/profile', (userData) => {
                console.log('[UserProfile] Perfil actualizado desde otro componente');
                this.user = userData;
                this.syncEditForm();
            });
        },

        /**
         * Cargar perfil de usuario
         */
        async loadProfile() {
            try {
                // Esta llamada probablemente usará cache si user-dashboard ya la hizo
                this.user = await this.apiCall('/api/user/profile', {
                    cache: true,
                    ttl: 10 * 60 * 1000 // 10 minutos
                });
                
                this.syncEditForm();
                
                console.log('[UserProfile] Perfil cargado');
                
            } catch (error) {
                console.error('[UserProfile] Error cargando perfil:', error);
            }
        },

        /**
         * Sincronizar formulario con datos actuales
         */
        syncEditForm() {
            if (this.user) {
                this.editForm = {
                    name: this.user.name || '',
                    email: this.user.email || '',
                    bio: this.user.bio || ''
                };
            }
        },

        /**
         * Iniciar edición
         */
        startEdit() {
            this.isEditing = true;
            this.syncEditForm();
        },

        /**
         * Cancelar edición
         */
        cancelEdit() {
            this.isEditing = false;
            this.syncEditForm();
        },

        /**
         * Guardar cambios
         */
        async saveProfile() {
            try {
                const result = await this.apiCall('/api/user/profile', {
                    method: 'PUT',
                    body: JSON.stringify(this.editForm),
                    cache: false
                });

                this.user = result;
                this.isEditing = false;
                
                // Automáticamente notificará a otros componentes suscritos
                console.log('[UserProfile] Perfil guardado');
                
                // Mostrar notificación de éxito
                window.Alpine.store('toast')?.success('Perfil actualizado correctamente');
                
            } catch (error) {
                console.error('[UserProfile] Error guardando perfil:', error);
                window.Alpine.store('toast')?.error('Error al actualizar perfil');
            }
        },

        /**
         * Validación del formulario
         */
        get isFormValid() {
            return this.editForm.name.trim() && 
                   this.editForm.email.trim() && 
                   this.editForm.email.includes('@');
        },

        /**
         * Detectar cambios en el formulario
         */
        get hasChanges() {
            if (!this.user) return false;
            
            return this.editForm.name !== (this.user.name || '') ||
                   this.editForm.email !== (this.user.email || '') ||
                   this.editForm.bio !== (this.user.bio || '');
        }
    }));
});