import { sidebarMenus } from './menus'

export function getFirstAccessibleRoute(user) {
    const permissions = user?.user?.permissions || []

    function findFirstRoute(menus) {
        for (const menu of menus) {
            if (menu.children && menu.children.length > 0) {
                const childRoute = findFirstRoute(menu.children)
                if (childRoute) return childRoute
            } else {
                if (permissions.some((perm) => perm.startsWith(menu.permission))) {
                    return menu.to
                }
            }
        }
        return null
    }

    return findFirstRoute(sidebarMenus)
}
