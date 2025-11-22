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
                key: 'academic-years',
                label: 'Tahun Ajaran',
                icon: 'fas fa-calendar',
                to: '/master-data/academic-years',
                permission: 'master_data.academic_years.view',
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
    },
    {
        key: 'attendance',
        label: 'Absensi Guru',
        icon: 'fas fa-user-clock',
        children: [
            {
                key: 'attendance-teacher',
                label: 'Absensi',
                icon: 'fas fa-clock',
                to: '/attendance/teacher',
                permission: 'attendance.teacher.view',
            },
            {
                key: 'attendance-history',
                label: 'History',
                icon: 'fas fa-history',
                to: '/attendance/history',
                permission: 'attendance.teacher.history',
            },
            {
                key: 'attendance-absence',
                label: 'Ketidakhadiran Guru',
                icon: 'fas fa-user-times',
                to: '/attendance/absence',
                permission: 'attendance.teacher.absence',
            }
        ]
    },
    {
        key: 'my-schedules',
        label: 'Jadwal Mengajar',
        icon: 'fas fa-calendar-alt',
        to: '/my-schedules',
        permission: 'schedules.view',
    },
    {
        key: 'student-attendance',
        label: 'Absensi Siswa',
        icon: 'fas fa-user-graduate',
        to: '/student-attendance',
        permission: 'student_attendances.view',
    },
    {
        key: 'teacher-journals',
        label: 'Jurnal Guru',
        icon: 'fas fa-journal-whills',
        to: '/teacher-journals',
        permission: 'teacher_journals.view',
    }
]
