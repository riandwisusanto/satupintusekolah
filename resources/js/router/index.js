import { createRouter, createWebHistory } from 'vue-router'
import { alertError } from '../lib/alert.js'
import { getFirstAccessibleRoute } from '../lib/menus/firstAccessMenu.js'

// Modular Routes
import { DasboardRoutes } from '../views/dashboard/routes.js'

// System Routes
import { ErrorRoutes } from '../views/errors/routes.js'
import { AuthRoutes } from '../views/auth/routes.js'
import { DeveloperRoutes } from '../views/developer/routes.js'

// Route children dari Main Layout
const MainChildren = [
    ...DasboardRoutes,
]

const routesMain = {
    path: '',
    name: 'main',
    component: () => import('../views/main.vue'),
    children: MainChildren,
    meta: { requiresAuth: true },
}

const routes = [
    {
        path: '',
        redirect: () => {
            const token = localStorage.getItem('token')
            if (!token) return '/login'

            const user = JSON.parse(localStorage.getItem('user'))
            const route = getFirstAccessibleRoute(user)

            return route || '/forbidden'
        },
    },
    ...AuthRoutes,
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

// Global Navigation Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')
    const isAuthenticated = !!token

    if (to.meta.requiresAuth && !isAuthenticated) {
        alertError('Sesi telah habis')
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        return next('/login')
    }

    if (to.meta.permission) {
        const user = JSON.parse(localStorage.getItem('user') || '{}')
        const permissions = user?.permissions || []

        if (!permissions.includes(to.meta.permission)) {
            return next('/forbidden')
        }
    }

    next()
})

export default router
