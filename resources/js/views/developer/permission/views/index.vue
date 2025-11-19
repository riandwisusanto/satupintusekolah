<script setup>
import { defineAsyncComponent, ref } from 'vue'
import { usePermission } from '@/lib/permission'
import { alertError, alertSuccess, alretConfirm } from '@/lib/alert'
import { apiRequest } from '@/lib/apiClient'

const FormAdd = defineAsyncComponent(() => import('../form/FormRole.vue'))

const tableRef = ref()
const { checkPermission } = usePermission()
const drawer = ref(false)
const selected = ref(null)
const loading = ref(false)

const columns = [
    { field: 'label', display: 'Role' },
    { field: 'users', display: 'Pengguna', sortable: false },
    { field: 'action', display: 'Action', sortable: false },
]

const deleteItem = async (id) => {
    alretConfirm('delete').then(async (result) => {
        if (result.isConfirmed) {
            const { ok, data, error } = await apiRequest(`roles/${id}`, {
                method: 'delete',
            })

            if (ok) {
                alertSuccess('Role berhasil dihapus')
                tableRef.value?.reload()
            } else {
                alertError(error)
            }
        }
    })
}

const addItem = () => {
    selected.value = null
    drawer.value = true
}

const editItem = (row) => {
    selected.value = row
    drawer.value = true
}

const save = async (row) => {
    alretConfirm('save').then(async (result) => {
        if (result.isConfirmed) {
            loading.value = true
            let endpoint = `roles`
            if (row.id) {
                endpoint = `roles/${row.id}`
            }
            const { ok, data, error } = await apiRequest(endpoint, {
                method: row.id ? 'put' : 'post',
                body: {
                    name: row.label,
                    permissions: row.permissions,
                },
            })

            if (ok) {
                alertSuccess('Role berhasil ' + (row.id ? 'diubah' : 'ditambahkan'))
                drawer.value = false
                tableRef.value?.reload()
            } else {
                alertError(error)
            }

            loading.value = false
        }
    })
}
</script>

<template>
    <section class="content-header">
        <h5 class="text-center">Developer Permission</h5>
    </section>
    <section class="content">
        <div class="card">
            <div class="position-relative">
                <TableServerSide
                    ref="tableRef"
                    title=""
                    :show-add-button="true"
                    :show-drawer="true"
                    :columns="columns"
                    :initial-sort="{ field: 'code', order: 'desc' }"
                    :per_page="10"
                    endpoint="roles"
                    :extra="{
                        with: 'users,permissions',
                    }"
                    :loading="loading"
                    @open-drawer="addItem"
                >
                    <template #cell-users="{ row }">
                        <span v-if="row.users.length == 0">-</span>
                        <span v-else>{{ row.users.length }}</span>
                    </template>
                    <template #cell-action="{ row }">
                        <div class="btn-group">
                            <button
                                class="btn btn-primary btn-sm"
                                type="button"
                                @click.stop.prevent="editItem(row)"
                            >
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                class="btn btn-danger btn-sm"
                                type="button"
                                @click.stop.prevent="deleteItem(row.id)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </TableServerSide>

                <!-- Drawer with fixed positioning -->
                <FormAdd
                    v-model:visible="drawer"
                    :selected="selected"
                    class="fixed top-0 right-0 z-50"
                    @save="save"
                />
            </div>
        </div>
    </section>
</template>
