export const AttendanceRoutes = [
    {
        path: 'attendance/teacher',
        name: 'attendance.teacher',
        component: () => import('./views/index.vue'),
    },
    {
        path: 'attendance/teacher/history',
        name: 'attendance.teacher.history',
        component: () => import('./views/history.vue'),
    }
]
