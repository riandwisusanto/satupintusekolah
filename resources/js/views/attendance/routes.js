export const AttendanceRoutes = [
    {
        path: 'attendance/teacher',
        name: 'attendance.teacher',
        component: () => import('./views/index.vue'),
    },
    {
        path: 'attendance/history',
        name: 'attendance.history',
        component: () => import('./views/history.vue'),
    },
    {
        path: 'attendance/absence',
        name: 'attendance.absence',
        component: () => import('./views/absence.vue'),
    }
]
