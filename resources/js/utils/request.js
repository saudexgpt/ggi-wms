import axios from 'axios';
import { Message } from 'element-ui';
import { getToken, setToken } from '@/utils/auth';

// Create axios instance
const service = axios.create({
  baseURL: process.env.MIX_BASE_API,
  timeout: 300000, // Request timeout
});

// Request intercepter
service.interceptors.request.use(
  config => {
    const token = getToken();
    if (token) {
      config.headers['Authorization'] = 'Bearer ' + getToken(); // Set JWT token
    }

    return config;
  },
  error => {
    // Do something with request error
    console.log(error); // for debug
    Promise.reject(error);
  }
);

// response pre-processing
service.interceptors.response.use(
  response => {
    if (response.headers.authorization) {
      setToken(response.headers.authorization);
      response.data.token = response.headers.authorization;
    }

    return response.data;
  },
  error => {
    let message = error.message;
    if (error.response.data && error.response.data.messages) {
      message = error.response.data.messages;
    } else if (error.response.data && error.response.data.message) {
      message = error.response.data.message;
    }
    console.log(message);
    Message({
      message: message,
      type: 'error',
      duration: 10 * 1000,
    });
    return Promise.reject(error);
  },
);

export default service;
