import { getCurrentInstance } from 'vue'

export function useGlobalComponent(name) {
    const instance = getCurrentInstance()
    return instance?.appContext.config.globalProperties.$resolveComponent(name)
}
