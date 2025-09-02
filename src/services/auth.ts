import api from '@/lib/api';
import { ApiResponse, User } from '@/types';

export const authService = {
     login: async (email: string, password: string): Promise<{ user: User; token: string }> => {
          const response = await api.post<ApiResponse<{ user: User; token: string }>>('/auth/login', {
               email,
               password
          });
          return response.data.data;
     },

     logout: async (): Promise<void> => {
          await api.post('/auth/logout');
          localStorage.removeItem('auth_token');
     },

     getCurrentUser: async (): Promise<User> => {
          const response = await api.get<ApiResponse<User>>('/auth/me');
          return response.data.data;
     }
};
