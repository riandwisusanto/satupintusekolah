export const ErrorRoutes = [
    {
        path: '/forbidden',
        name: 'forbidden',
        component: () => import('./forbidden.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/forbidden',
    },
]
