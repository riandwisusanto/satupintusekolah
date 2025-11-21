<script setup>
import { watch, ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useUser } from '../store.js'
import { usePermission } from '../lib/permission.js'
import { sidebarMenus } from '../lib/menus/menus.js'
const credential = useUser()

const menus = credential.menus
const { startWithPermission } = usePermission()
const route = useRoute()
const openMenu = ref('')

function filterMenus(items) {
    return items
        .map((item) => {
            if (item.children) {
                const filteredChildren = filterMenus(item.children)
                if (filteredChildren.length) {
                    return { ...item, children: filteredChildren }
                }
                return null
            } else if (!item.permission || startWithPermission(item.permission)) {
                return item
            }
            return null
        })
        .filter(Boolean)
}

const filteredMenus = computed(() => filterMenus(sidebarMenus))

const beforeEnter = (el) => {
    el.style.height = '0'
    el.style.opacity = '0'
    el.style.transition = 'height 0.3s ease, opacity 0.3s ease'
}

const enter = (el) => {
    const height = el.scrollHeight
    el.style.height = height + 'px'
    el.style.opacity = '1'
}

const afterEnter = (el) => {
    el.style.height = 'auto'
}

const beforeLeave = (el) => {
    el.style.height = el.scrollHeight + 'px'
    el.style.opacity = '1'
    el.style.transition = 'height 0.6s ease, opacity 0.5s ease'
}

const leave = (el) => {
    requestAnimationFrame(() => {
        el.style.height = '0'
        el.style.opacity = '0'
    })
}

const afterLeave = (el) => {
    el.style.height = 'auto'
}

watch(
    () => route.path,
    (newPath) => {
        // cari parent berdasarkan anak
        const parent = sidebarMenus.find(menu => {
            if (!menu.children) return false
            return menu.children.some(child => newPath.startsWith(child.to))
        })

        // jika ketemu parent → buka parent
        if (parent) {
            openMenu.value = parent.key
            return
        }

        // fallback ke menu single (tanpa children)
        const single = sidebarMenus.find(menu => {
            return !menu.children && newPath.startsWith(menu.to)
        })

        openMenu.value = single ? single.key : ''
    },
    { immediate: true }
)
</script>

<template>
    <!-- Main Sidebar Container -->
    <aside
        class="main-sidebar sidebar-dark-primary elevation-4"
        style="background: linear-gradient(to bottom, #001f3f, #003366);"
    >
        <!-- Brand Logo -->
        <a href="/" class="brand-link text-center">
            <img
                src="/public/assets/images/logo-jago.png"
                alt="Logo"
                class="brand-image elevation-3"
            />
            <span class="brand-text font-weight-light text-white"><b>Satu Pintu Sekolah</b></span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li
                        v-for="menu in filteredMenus"
                        :key="menu.key"
                        class="nav-item"
                        :class="{
                            'has-treeview': menu.children,
                            'menu-open': openMenu === menu.key,
                        }"
                    >
                        <!-- Jika TIDAK punya children, langsung RouterLink -->
                        <RouterLink
                            v-if="!menu.children"
                            :to="menu.to"
                            class="nav-link"
                            :class="{ active: route.path.startsWith(menu.to) }"
                        >
                            <i :class="['nav-icon', menu.icon]"></i>
                            <p>{{ menu.label }}</p>
                        </RouterLink>

                        <!-- Jika PUNYA children, gunakan dropdown toggle -->
                        <template v-else>
                            <a
                                href="#"
                                class="nav-link"
                                :class="{ active: openMenu === menu.key }"
                                @click.prevent="openMenu = openMenu === menu.key ? '' : menu.key"
                            >
                                <i :class="['nav-icon', menu.icon]"></i>
                                <p>
                                    {{ menu.label }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <transition
                                @before-enter="beforeEnter"
                                @enter="enter"
                                @after-enter="afterEnter"
                                @before-leave="beforeLeave"
                                @leave="leave"
                                @after-leave="afterLeave"
                            >
                                <ul v-show="openMenu === menu.key" class="nav nav-treeview pl-3">
                                    <li
                                        v-for="child in menu.children"
                                        :key="child.to"
                                        class="nav-item"
                                    >
                                        <RouterLink
                                            :to="child.to"
                                            class="nav-link"
                                            :class="{ active: route.path.startsWith(child.to) }"
                                        >
                                            <i :class="['nav-icon', child.icon]"></i>
                                            <p style="font-size: 13.5px">{{ child.label }}</p>
                                        </RouterLink>
                                    </li>
                                </ul>
                            </transition>
                        </template>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
</template>

<style scoped>
.sidebar {
    max-height: calc(100vh - 56px);
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 1rem;
}

.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.sidebar .nav-link,
.sidebar .brand-text {
    color: #ffffff !important;
    font-weight: 400;
}

.nav-sidebar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.15) !important;
    color: #fff !important;
}

.nav-sidebar .nav-link.active .nav-icon {
    color: #fff !important;
}

.nav-treeview .nav-link.active {
    background-color: rgba(255, 255, 255, 0.22) !important;
    font-weight: 600;
}

.sidebar .nav-sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
}

.sidebar .nav-treeview .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
}

.sidebar .nav-sidebar .nav-link,
.sidebar .nav-treeview .nav-link {
    transition:
        background-color 0.2s ease,
        color 0.2s ease;
}
</style>
