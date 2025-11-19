import './bootstrap.js'

import { createApp, defineAsyncComponent } from 'vue'
import App from './App.vue'
import router from './router/index.js'
import { createPinia } from 'pinia'
import { useLocalStorage } from '@vueuse/core'
import { useUser } from './store.js'
import { LoadingPlugin } from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import piniaPersistedstate from 'pinia-plugin-persistedstate'

const pinia = createPinia()
pinia.use(piniaPersistedstate)
const app = createApp(App)

const globalComponents = import.meta.glob('@/components/*.vue')

for (const [path, loader] of Object.entries(globalComponents)) {
    const name = path.split('/').pop().replace('.vue', '')
    app.component(name, defineAsyncComponent(loader))
}
app.config.globalProperties.$resolveComponent = (name) => {
    return app._context.components[name] || null
}

app.use(pinia)
app.use(router)
app.use(LoadingPlugin)
app.mount('#app')

const token = useLocalStorage('token', '')
if (token.value) {
    const credential = useUser()
    const token_load = JSON.parse(token.value)
    // store to pinia
    credential.user.accesstoken = token_load.token
    credential.user.name = token_load.user.name
    credential.user.role = token_load.user.roles
    credential.user.permissions = token_load.user.permissions
}
