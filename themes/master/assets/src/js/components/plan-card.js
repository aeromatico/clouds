/**
 * Plan Card Component for pricing selection
 */
export default function planCard(config) {
    return {
        selectedCycle: config.defaultCycle || 'monthly',
        pricingOptions: config.pricingOptions || [],
        planId: config.planId,
        planName: config.planName,
        serviceName: config.serviceName,
        features: config.features || [],

        init() {
            // Component initialized
        },

        getCurrentPrice() {
            const option = this.pricingOptions.find(p => p.cycle === this.selectedCycle);
            return option ? parseFloat(option.price).toFixed(2) : '0.00';
        },

        getCycleLabel() {
            const option = this.pricingOptions.find(p => p.cycle === this.selectedCycle);
            return option ? option.label : '';
        },

        getSetupFee() {
            const option = this.pricingOptions.find(p => p.cycle === this.selectedCycle);
            return option ? parseFloat(option.setup_fee || 0) : 0;
        },

        addToCart() {
            // Verificar que el carrito existe
            if (typeof window.cart === 'undefined') {
                console.error('Cart object not found on window');
                this.showError('Error: Sistema de carrito no disponible');
                return;
            }

            const price = parseFloat(this.getCurrentPrice());

            try {
                window.cart.addItem(
                    this.planId,
                    this.planName,
                    this.serviceName,
                    this.selectedCycle,
                    price,
                    1,
                    this.features
                );

                this.showSuccess('Plan agregado al carrito exitosamente');
            } catch (error) {
                console.error('Error adding to cart:', error);
                this.showError('Error al agregar al carrito: ' + error.message);
            }
        },

        showSuccess(message) {
            if (typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('toast')) {
                Alpine.store('toast').success(message);
            } else {
                alert(message);
            }
        },

        showError(message) {
            if (typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('toast')) {
                Alpine.store('toast').error(message);
            } else {
                alert(message);
            }
        }
    };
}
