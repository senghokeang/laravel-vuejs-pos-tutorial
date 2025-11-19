import './bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import 'bootstrap';
import '../css/style.css';
import '../css/app.css';

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'

const app = createApp();
app.use(createPinia())
app.use(router)
app.mount('#app')