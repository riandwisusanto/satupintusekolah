export const DeveloperRoutes = [
    {
        path: '/developer/logs',
        name: 'logs',
        component: () => import('./index.vue'),
    },
    {
        path: '/developer/permission',
        name: 'permissions',
        component: () => import('./permission/views/index.vue'),
    }
]
