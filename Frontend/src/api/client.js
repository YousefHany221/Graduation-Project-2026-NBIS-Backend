import axios from 'axios';

const client = axios.create({
  // ✅ عدلنا الرابط هنا عشان يكلم السيرفر الأونلاين مباشرة
  baseURL: 'https://graduation-project-2026-nbis-backend-2.onrender.com/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true
});

// Request interceptor - attach token on every call
client.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('nbis_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Let the browser set proper multipart boundaries for FormData
    if (config.data instanceof FormData) {
      delete config.headers['Content-Type'];
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor - on 401, clear token and redirect to login
client.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('nbis_token');
      localStorage.removeItem('nbis_user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default client;