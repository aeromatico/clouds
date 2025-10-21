/**
 * Shopping Cart Management with localStorage
 */

export default class ShoppingCart {
    constructor() {
        this.storageKey = 'clouds_cart';
        this.init();
    }

    init() {
        // Initialize cart from localStorage
        if (!localStorage.getItem(this.storageKey)) {
            localStorage.setItem(this.storageKey, JSON.stringify({}));
        }
    }

    /**
     * Get all cart items
     */
    getItems() {
        const cart = localStorage.getItem(this.storageKey);
        return cart ? JSON.parse(cart) : {};
    }

    /**
     * Get cart count (number of unique items)
     */
    getCount() {
        const items = this.getItems();
        return Object.keys(items).length;
    }

    /**
     * Get total quantity (sum of all quantities)
     */
    getTotalQuantity() {
        const items = this.getItems();
        let total = 0;
        for (let key in items) {
            total += items[key].quantity || 1;
        }
        return total;
    }

    /**
     * Get cart total price
     */
    getTotal() {
        const items = this.getItems();
        let total = 0;
        for (let key in items) {
            const item = items[key];
            total += (item.price || 0) * (item.quantity || 1);
        }
        return total;
    }

    /**
     * Add item to cart
     */
    addItem(planId, planName, serviceName, billingCycle, price, quantity = 1, features = []) {
        const items = this.getItems();
        const itemKey = `${planId}_${billingCycle}`;

        if (items[itemKey]) {
            // Update quantity if item exists
            items[itemKey].quantity += quantity;
        } else {
            // Add new item
            items[itemKey] = {
                plan_id: planId,
                plan_name: planName,
                service_name: serviceName,
                billing_cycle: billingCycle,
                price: price,
                quantity: quantity,
                features: features
            };
        }

        localStorage.setItem(this.storageKey, JSON.stringify(items));
        this.dispatchUpdateEvent();
        return this.getCount();
    }

    /**
     * Remove item from cart
     */
    removeItem(itemKey) {
        const items = this.getItems();
        delete items[itemKey];
        localStorage.setItem(this.storageKey, JSON.stringify(items));
        this.dispatchUpdateEvent();
    }

    /**
     * Update item quantity
     */
    updateQuantity(itemKey, quantity) {
        const items = this.getItems();
        if (items[itemKey] && quantity > 0) {
            items[itemKey].quantity = quantity;
            localStorage.setItem(this.storageKey, JSON.stringify(items));
            this.dispatchUpdateEvent();
        }
    }

    /**
     * Clear cart
     */
    clear() {
        localStorage.setItem(this.storageKey, JSON.stringify({}));
        this.dispatchUpdateEvent();
    }

    /**
     * Dispatch cart updated event
     */
    dispatchUpdateEvent() {
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: {
                count: this.getCount(),
                totalQuantity: this.getTotalQuantity(),
                total: this.getTotal(),
                items: this.getItems()
            }
        }));
    }

    /**
     * Get cart data for checkout
     */
    getCheckoutData() {
        return {
            items: this.getItems(),
            count: this.getCount(),
            total: this.getTotal()
        };
    }
}

// Export for use in app.js (cart instance will be created there)
// window.cart = new ShoppingCart();
