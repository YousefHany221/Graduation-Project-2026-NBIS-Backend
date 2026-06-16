import axios from 'axios';

const client = axios.create({
  // ✅ الرابط الأونلاين المظبوط للـ API
  baseURL: 'https://graduation-project-2026-nbis-backend-2.onrender.com/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  withCredentials: true // ✅ أساسية جداً لنقل الكوكيز والـ Tokens بين الدومينات أونلاين
});

// 🔒 دالة سحرية لطلب الـ CSRF Token قبل الـ Login أو الـ Register لمنع الـ CORS/CSRF Block
export const ensureCsrf = () => {
  // بنطلبها من مسار الـ sanctum مباشرة (بره الـ /api)
  return axios.get('https://graduation-project-2026-nbis-backend-2.onrender.com/sanctum/csrf-cookie', {
    withCredentials: true
  });
};

// Request interceptor - attach token on every call
client.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('nbis_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Let the browser set proper multipart boundaries for FormData (للأقسام اللي فيها رفع بصمات أو صور)
    if (config.data instanceof FormData) {
      delete config.headers['Content-Type'];
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
client.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('nbis_token');
      localStorage.removeItem('nbis_user');
      // ✅ عدلنا دي عشان تتماشى مع الـ Netlify Redirects اللي عملناها ومن غير ما تضرب 404
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default client;