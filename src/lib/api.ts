import axios from 'axios';

const api = axios.create({
     baseURL: process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api',
     timeout: 10000,
});

// Request interceptor
api.interceptors.request.use(
     (config) => {
          const token = localStorage.getItem('auth_token');
          if (token) {
               config.headers.Authorization = `Bearer ${token}`;
          }
          return config;
     },
     (error) => {
          return Promise.reject(error);
     }
);

// Response interceptor
api.interceptors.response.use(
     (response) => response,
     (error) => {
          if (error.response?.status === 401) {
               localStorage.removeItem('auth_token');
               window.location.href = '/login';
          }
          return Promise.reject(error);
     }
);

export default api;
