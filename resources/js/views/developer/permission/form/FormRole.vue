<template>
    <!-- Overlay -->
    <transition name="fade">
        <div v-if="props.visible" class="drawer-overlay" @click.self="close" />
    </transition>

    <!-- Drawer -->
    <transition name="slide">
        <aside v-if="props.visible" class="drawer-panel card shadow-lg overflow-auto px-4 py-3">
            <!-- Header -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-tool" @click="close">
                    <i class="fas fa-times" />
                </button>
                <h5 class="mb-0">{{ title }}</h5>
            </div>

            <!-- Body -->
            <form @submit.prevent="saveItem">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <FormInput
                                v-model="form.label"
                                label="Nama"
                                type="text"
                                autofocus
                                required
                            />
                        </div>
                        <div class="col-md-12">
                            <PermissionTree
                                v-model="form.permissions"
                                :permissions="nestedPermissions"
                            />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer d-flex justify-content-end fixed-bottom">
                    <button type="button" class="btn btn-secondary mr-2" @click="close">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ selected ? 'Ubah' : 'Tambah' }}
                    </button>
                </div>
            </form>
        </aside>
    </transition>
</template>

<script setup>
import { reactive, watch, computed, ref } from 'vue'
import FormInput from '@/components/FormInput.vue'
import PermissionTree from './PermissionTree.vue'
import { apiRequest } from '@/lib/apiClient'

const rawPermissions = ref([])

const fetchPermission = async () => {
    const { ok, data } = await apiRequest('permissions/options')

    if (ok) {
        const { permissions } = data?.data
        rawPermissions.value.push(...permissions)
    }
}

function buildPermissionTree(permissions) {
    const root = {}
    const labelMap = {}

    // Simpan label dari BE berdasarkan name
    for (const perm of permissions) {
        labelMap[perm.name] = perm.label
    }

    // Bangun tree
    for (const perm of permissions) {
        const parts = perm.name.split('.') // ['configuration', 'branch', 'view']
        const labelParts = (labelMap[perm.name] || '').split(' ') // ['Konfig', 'Cabang', 'Lihat']
        let current = root

        for (let i = 0; i < parts.length; i++) {
            const part = parts[i]
            const path = parts.slice(0, i + 1).join('.')

            const nodeLabel =
                labelParts[i].split('_').join(' ') || part.charAt(0).toUpperCase() + part.slice(1)

            if (!current[part]) {
                current[part] = {
                    __meta__: {
                        label: nodeLabel,
                        value: i === parts.length - 1 ? perm.name : null,
                    },
                    __children__: {},
                }
            }

            current = current[part].__children__
        }
    }

    // Convert ke tree array
    function toTree(node) {
        return Object.entries(node).map(([key, value]) => {
            const item = {
                label: value.__meta__.label,
            }

            if (value.__meta__.value) {
                item.value = value.__meta__.value
            }

            const children = toTree(value.__children__)
            if (children.length) item.children = children

            return item
        })
    }

    return toTree(root)
}

const nestedPermissions = computed(() => buildPermissionTree(rawPermissions.value))

const props = defineProps({
    selected: {
        type: Object,
        default: null,
    },
    visible: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:visible', 'save'])
const title = computed(() => (props.selected ? 'Ubah Role' : 'Tambah Role'))

function close() {
    emit('update:visible', false)
    resetForm()
}

// Local form state
const form = reactive({
    id: null,
    label: '',
    permissions: [],
})

function fillForm(src) {
    if (!src) return
    Object.assign(form, {
        ...src,
        permissions: src.permissions.map((p) => (typeof p === 'string' ? p : p.name)),
    })
}

watch(
    () => props.visible,
    async (val) => {
        await fetchPermission()

        if (val && props.selected) {
            fillForm(props.selected)
        } else if (!val) {
            resetForm()
        }
    }
)

function resetForm() {
    Object.assign(form, {
        id: null,
        label: '',
    })
}

function saveItem() {
    emit('save', { ...form })
}

defineExpose({
    close,
})
</script>

<style scoped>
/* Overlay */
.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1040;
    overflow: hidden;
}

.card-body {
    padding-bottom: 80px;
}

/* Drawer panel */
.drawer-panel {
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
    width: 100%;
    max-width: 50%;
    background: #fff;
    z-index: 1050;
    display: flex;
    flex-direction: column;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-enter-from {
    transform: translateX(100%);
}
.slide-leave-to {
    transform: translateX(100%);
}
</style>
