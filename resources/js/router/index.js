import { createRouter, createWebHistory } from 'vue-router';
import LoginForm from '../Components/LoginForm.vue';
import Dashboard from '../Components/Dashboard.vue';

const routes = [
    { path: '/', component: LoginForm },
    { path: '/dashboard', component: Dashboard },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
