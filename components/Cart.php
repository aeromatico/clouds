<?php namespace Aero\Clouds\Components;

use Cms\Classes\ComponentBase;
use Aero\Clouds\Models\Plan;
use Aero\Clouds\Models\PaymentGateway;
use Session;
use Flash;

/**
 * Cart Component
 */
class Cart extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Shopping Cart',
            'description' => 'Manages shopping cart functionality'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    /**
     * Get cart items from localStorage (via JavaScript)
     * This is populated on the client-side
     */
    public function getCartItems()
    {
        // Cart is now managed in localStorage
        // This returns empty array, items are retrieved via JavaScript
        return [];
    }

    /**
     * Get cart count - number of unique items
     */
    public function getCartCount()
    {
        // Cart count is managed in localStorage via JavaScript
        return 0;
    }

    /**
     * Get cart total
     */
    public function getCartTotal()
    {
        $items = $this->getCartItems();
        $total = 0;

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? 0;
            $total += $price * $quantity;
        }

        return $total;
    }

    /**
     * Add item to cart
     */
    public function onAddToCart()
    {
        $planId = post('plan_id');
        $billingCycle = post('billing_cycle', 'monthly');
        $quantity = post('quantity', 1);

        $plan = Plan::with('service')->find($planId);

        if (!$plan) {
            Flash::error('Plan not found');
            return;
        }

        $cart = $this->getCartItems();

        // Create unique key for cart item
        $itemKey = $planId . '_' . $billingCycle;

        // Get price based on billing cycle
        $price = $this->getPlanPrice($plan, $billingCycle);

        if (isset($cart[$itemKey])) {
            // Update quantity if item exists
            $cart[$itemKey]['quantity'] += $quantity;
        } else {
            // Add new item
            $cart[$itemKey] = [
                'plan_id' => $planId,
                'plan_name' => $plan->name,
                'service_name' => $plan->service ? $plan->service->name : '',
                'billing_cycle' => $billingCycle,
                'price' => $price,
                'quantity' => $quantity,
                'features' => $plan->features_list ?? [],
            ];
        }

        Session::put('cart', $cart);
        Flash::success('Plan added to cart');

        return [
            'count' => $this->getCartCount(),
            'total' => $this->getCartTotal()
        ];
    }

    /**
     * Remove item from cart
     */
    public function onRemoveFromCart()
    {
        $itemKey = post('item_key');
        $cart = $this->getCartItems();

        if (isset($cart[$itemKey])) {
            unset($cart[$itemKey]);
            Session::put('cart', $cart);
            Flash::success('Item removed from cart');
        }

        return [
            '#cart-items' => $this->renderPartial('@items'),
            'count' => $this->getCartCount(),
            'total' => $this->getCartTotal()
        ];
    }

    /**
     * Update cart item quantity
     */
    public function onUpdateQuantity()
    {
        $itemKey = post('item_key');
        $quantity = post('quantity', 1);

        $cart = $this->getCartItems();

        if (isset($cart[$itemKey]) && $quantity > 0) {
            $cart[$itemKey]['quantity'] = $quantity;
            Session::put('cart', $cart);
        }

        return [
            '#cart-items' => $this->renderPartial('@items'),
            'count' => $this->getCartCount(),
            'total' => $this->getCartTotal()
        ];
    }

    /**
     * Clear cart
     */
    public function onClearCart()
    {
        Session::forget('cart');
        Flash::success('Cart cleared');

        return [
            '#cart-items' => $this->renderPartial('@items'),
            'count' => 0,
            'total' => 0
        ];
    }

    /**
     * Get plan price based on billing cycle
     */
    protected function getPlanPrice($plan, $billingCycle)
    {
        switch ($billingCycle) {
            case 'monthly':
                return $plan->price;
            case 'quarterly':
                return $plan->quarterly_price ?? ($plan->price * 3);
            case 'semi_annually':
                return $plan->semi_annually_price ?? ($plan->price * 6);
            case 'annually':
                return $plan->annually_price ?? ($plan->price * 12);
            case 'biennially':
                return $plan->biennially_price ?? ($plan->price * 24);
            default:
                return $plan->price;
        }
    }

    /**
     * Get billing cycle label
     */
    public function getBillingCycleLabel($cycle)
    {
        $labels = [
            'monthly' => 'Mensual',
            'quarterly' => 'Trimestral',
            'semi_annually' => 'Semestral',
            'annually' => 'Anual',
            'biennially' => 'Bienal'
        ];

        return $labels[$cycle] ?? $cycle;
    }

    /**
     * Component initialization
     */
    public function onRun()
    {
        $this->page['cartItems'] = $this->getCartItems();
        $this->page['cartCount'] = $this->getCartCount();
        $this->page['cartTotal'] = $this->getCartTotal();
        $this->page['paymentGateways'] = PaymentGateway::isActive()
            ->orderBy('sort_order')
            ->get();
    }
}
