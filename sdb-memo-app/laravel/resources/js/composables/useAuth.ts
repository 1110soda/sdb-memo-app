import { ref } from 'vue';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    email: string;
}

const user = ref<User | null>(null);

export function useAuth() {
    const checkAuth = async() => {
        if (user.value) return;

        try {
            const response = await axios.get('/api/user');
            user.value = response.data;
        } catch (error) {
            user.value = null;
        }
    };

    const logout = async() => {
        try {
            await axios.post('/api/logout');
            user.value = null;
        } catch (error) {
            console.error('Logout failed:', error);
            user.value = null;
        }
    };

    return {
        user,
        checkAuth,
        logout,
    };
}

