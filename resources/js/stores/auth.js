import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Auth Store (Pinia)
 * 
 * SOLID - Single Responsibility
 * Manages authentication state and operations
 */
export const useAuthStore = defineStore('auth', () => {
    // State
    const user = ref(null);
    const token = ref(localStorage.getItem('auth_token'));
    const loading = ref(false);
    const error = ref(null);

    // Computed
    const isAuthenticated = computed(() => !!token.value && !!user.value);
    const userRole = computed(() => user.value?.role);

    /**
     * Login user
     * 
     * @param {string} email
     * @param {string} password
     * @returns {Promise}
     */
    const login = async (credentials) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/auth/login', credentials);

            if (response.data.success) {
                // Store token
                token.value = response.data.token;
                localStorage.setItem('auth_token', token.value);

                // Set axios default header
                axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

                // Store user data
                user.value = response.data.user;
                localStorage.setItem('user', JSON.stringify(user.value));

                return response.data;
            }

            throw new Error(response.data.message || 'Login failed');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Register student
     * 
     * @param {object} data Registration data
     * @returns {Promise}
     */
    const registerStudent = async (data) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/auth/register-student', data);

            if (response.data.success) {
                return response.data;
            }

            throw new Error(response.data.message || 'Registration failed');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Logout user
     * 
     * @returns {Promise}
     */
    const logout = async () => {
        try {
            await axios.post('/api/v1/auth/logout');
        } finally {
            // Always clear local state even if API call fails
            token.value = null;
            user.value = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
        }
    };

    /**
     * Get current user profile
     * 
     * @returns {Promise}
     */
    const getCurrentUser = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get('/api/v1/profile');

            if (response.data.success) {
                user.value = response.data.user;
                localStorage.setItem('user', JSON.stringify(user.value));
                return user.value;
            }

            throw new Error('Failed to fetch user profile');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Update user profile
     * 
     * @param {object} data
     * @returns {Promise}
     */
    const updateProfile = async (data) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.put('/api/v1/profile', data);

            if (response.data.success) {
                user.value = response.data.user;
                localStorage.setItem('user', JSON.stringify(user.value));
                return response.data;
            }

            throw new Error(response.data.message || 'Update failed');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Request password reset
     * 
     * @param {string} email
     * @returns {Promise}
     */
    const forgotPassword = async (email) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/auth/forgot-password', { email });

            if (response.data.success) {
                return response.data;
            }

            throw new Error(response.data.message || 'Request failed');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Reset password with token
     * 
     * @param {object} data
     * @returns {Promise}
     */
    const resetPassword = async (data) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/v1/auth/reset-password', data);

            if (response.data.success) {
                return response.data;
            }

            throw new Error(response.data.message || 'Reset failed');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Initialize auth state from localStorage
     */
    const initializeAuth = () => {
        const storedToken = localStorage.getItem('auth_token');
        const storedUser = localStorage.getItem('user');

        if (storedToken && storedUser) {
            token.value = storedToken;
            user.value = JSON.parse(storedUser);
            axios.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
        }
    };

    return {
        // State
        user,
        token,
        loading,
        error,

        // Computed
        isAuthenticated,
        userRole,

        // Methods
        login,
        registerStudent,
        logout,
        getCurrentUser,
        updateProfile,
        forgotPassword,
        resetPassword,
        initializeAuth,
    };
});
