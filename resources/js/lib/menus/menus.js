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
        ],
    }
]
