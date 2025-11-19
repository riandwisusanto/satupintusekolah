export const AuthRoutes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('./views/LoginForm.vue'), // <- Lazy load
    },
]
