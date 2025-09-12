import api from '@/lib/api';
import { DashboardExam, User } from '@/types';

export const authService = {
     login: async (email: string, password: string): Promise<{ user: User; token: string }> => {
          const response = await api.post<{ user: User; token: string }>('/login', {
               email,
               password
          });
          if (typeof window !== 'undefined' && response.data.token) {
               localStorage.setItem('api_token', response.data.token);
          }
          console.log("data login", response.data);
          return response.data;
     },

     logout: async (): Promise<void> => {
          await api.post('/logout');
          if (typeof window !== 'undefined') {
               localStorage.removeItem('api_token');
               localStorage.removeItem('exam_id');
               localStorage.removeItem('exam_duration');
               localStorage.removeItem('exam_statuses');
          }
     },

     getCurrentUser: async (): Promise<DashboardExam> => {
          const response = await api.get('/siswa/dashboard', {
               headers: {
                    Authorization: `Bearer ${localStorage.getItem('api_token')}`
               }
          });
          console.log("data user", response.data);
          return response.data;
     }
};
