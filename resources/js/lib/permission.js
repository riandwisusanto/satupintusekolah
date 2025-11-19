import { useUser } from '../store'

export const usePermission = () => {
    const user = useUser()

    const checkPermission = (permission) => {
        return user?.user?.permissions?.includes(permission)
    }

    const startWithPermission = (permission) => {
        return user?.user?.permissions?.some((p) => p.startsWith(permission))
    }

    return {
        checkPermission,
        startWithPermission,
    }
}
