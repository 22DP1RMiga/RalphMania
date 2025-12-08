import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('user_token'));

    const isAuthenticated = computed(() => !!token.value);
    const isGuest = computed(() => !token.value);

    const setUser = (userData) => {
        user.value = userData;
    };

    const setToken = (newToken) => {
        token.value = newToken;
        localStorage.setItem('user_token', newToken);
        axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
    };

    const logout = () => {
        user.value = null;
        token.value = null;
        localStorage.removeItem('user_token');
        delete axios.defaults.headers.common['Authorization'];
    };

    const login = async (credentials) => {
        try {
            const response = await axios.post('/api/login', credentials);
            setToken(response.data.token);
            setUser(response.data.user);
            return response.data;
        } catch (error) {
            throw error;
        }
    };

    const register = async (userData) => {
        try {
            const response = await axios.post('/api/register', userData);
            setToken(response.data.token);
            setUser(response.data.user);
            return response.data;
        } catch (error) {
            throw error;
        }
    };

    return {
        user,
        token,
        isAuthenticated,
        isGuest,
        setUser,
        setToken,
        logout,
        login,
        register,
    };
});
