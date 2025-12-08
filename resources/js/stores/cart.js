import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useCartStore = defineStore('cart', () => {
    const items = ref([]);
    const loading = ref(false);

    const itemCount = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0));

    const subtotal = computed(() =>
        items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    );

    const shippingCost = computed(() => subtotal.value > 50 ? 0 : 5);

    const total = computed(() => subtotal.value + shippingCost.value);

    const addItem = async (product, quantity = 1) => {
        const existingItem = items.value.find(item => item.product_id === product.id);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            items.value.push({
                product_id: product.id,
                name: product.name_lv,
                price: product.price,
                image: product.image,
                quantity: quantity,
            });
        }

        await syncCart();
    };

    const removeItem = async (productId) => {
        items.value = items.value.filter(item => item.product_id !== productId);
        await syncCart();
    };

    const updateQuantity = async (productId, quantity) => {
        const item = items.value.find(item => item.product_id === productId);
        if (item) {
            item.quantity = quantity;
            await syncCart();
        }
    };

    const clearCart = () => {
        items.value = [];
        localStorage.removeItem('cart');
    };

    const syncCart = async () => {
        // Save to localStorage
        localStorage.setItem('cart', JSON.stringify(items.value));

        // Sync with server if authenticated
        try {
            await axios.post('/api/cart/sync', { items: items.value });
        } catch (error) {
            console.error('Cart sync error:', error);
        }
    };

    const loadCart = () => {
        const saved = localStorage.getItem('cart');
        if (saved) {
            items.value = JSON.parse(saved);
        }
    };

    return {
        items,
        loading,
        itemCount,
        subtotal,
        shippingCost,
        total,
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
        loadCart,
    };
});
