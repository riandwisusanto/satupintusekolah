export const ReportRoutes = [
    {
        path: 'reports/teacher-attendance',
        name: 'reports.teacher-attendance',
        component: () => import('./views/teacher-attendance.vue'),
    },
    {
        path: 'reports/teacher-journals',
        name: 'reports.teacher-journals',
        component: () => import('./views/teacher-journals.vue'),
    },
    {
        path: 'reports/student-attendance',
        name: 'reports.student-attendance',
        component: () => import('./views/student-attendance.vue'),
    }
]
