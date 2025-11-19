export const sidebarMenus = [
    {
        key: 'dashboard',
        label: 'Dashboard',
        icon: 'fas fa-home',
        to: '/dashboard',
        permission: 'dashboard',
    },
    {
        key: 'master-data',
        label: 'Master Data',
        icon: 'fas fa-database',
        children: [
            {
                key: 'users',
                label: 'Pengguna',
                icon: 'fas fa-users',
                to: '/master-data/users',
                permission: 'master_data.users.view',
            },
            {
                key: 'classrooms',
                label: 'Kelas',
                icon: 'fas fa-school',
                to: '/master-data/classrooms',
                permission: 'master_data.classrooms.view',
            },
            {
                key: 'students',
                label: 'Siswa',
                icon: 'fas fa-user-graduate',
                to: '/master-data/students',
                permission: 'master_data.students.view',
            },
            {
                key: 'subjects',
                label: 'Mata Pelajaran',
                icon: 'fas fa-book',
                to: '/master-data/subjects',
                permission: 'master_data.subjects.view',
            },
            {
                key: 'schedules',
                label: 'Jadwal',
                icon: 'fas fa-calendar-alt',
                to: '/master-data/schedules',
                permission: 'master_data.schedules.view',
            },
        ],
    }
]
