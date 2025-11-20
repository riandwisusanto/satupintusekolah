<script setup>
import { ref, h } from 'vue'
import { usePermission } from '@/lib/permission'
import { alertError, alertSuccess, alretConfirm } from '@/lib/alert'
import { apiRequest } from '@/lib/apiClient'
import FormAdd from '../components/form.vue'
import StatusTag from '@/components/StatusTag.vue'

const tableRef = ref()
const { checkPermission } = usePermission()
const drawer = ref(false)
const selected = ref(null)
const loading = ref(false)

const statusMap = {
    1: { text: 'Aktif', class: 'badge-success' },
    0: { text: 'Tidak Aktif', class: 'badge-danger' },
}

const columns = [
    { field: 'id', display: '#ID' },
    { field: 'email', display: 'Email' },
    { field: 'name', display: 'Nama' },
    { field: 'nip', display: 'NIP' },
    { field: 'phone', display: 'No HP' },
    { field: 'photo', display: 'Foto' },
    { field: 'active', display: 'Status' },
    { field: 'action', display: 'Action', sortable: false },
]

const deleteItem = async (id) => {
    alretConfirm('delete').then(async (result) => {
        if (result.isConfirmed) {
            const { ok, data, error } = await apiRequest(`users/${id}`, {
                method: 'delete',
            })

            if (ok) {
                alertSuccess(data.message)
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

function buildPayload(form) {
    const hasFile = form.photo instanceof File

    if (!hasFile) {
        delete form.photo
        return {
            type: 'json',
            body: {
                ...form,
            },
        }
    }

    const fd = new FormData()
    if (form.id)
        fd.append('_method', 'PUT')

    Object.keys(form).forEach((key) => {
        fd.append(key, form[key])
    })

    return { type: 'formdata', body: fd }
}

const handleSave = async (row) => {
    alretConfirm('save').then(async (result) => {
        if (result.isConfirmed) {
            save(row)
        }
    })
}

const save = async (row) => {
    loading.value = true
    const { type, body } = buildPayload(row)

    const { ok, data, error } = await apiRequest('users' + (row.id ? `/${row.id}` : ''), {
        method: type === 'json' && row.id ? 'put' : 'post',
        body,
        headers: type === 'json' ? {} : { 'Content-Type': 'multipart/form-data' },
    })
    if (ok) {
        alertSuccess('Data berhasil disimpan')
        drawer.value = false
        tableRef.value?.reload()
    } else {
        alertError(error)
    }

    loading.value = false
}
</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4>Data Pengguna</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Pengguna</a></li>
                        <li class="breadcrumb-item active">Data Pengguna</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <TableServerSide
            ref="tableRef"
            title=""
            :show-add-button="checkPermission('master_data.users.create')"
            :show-drawer="true"
            :columns="columns"
            :initial-sort="{ field: 'id', order: 'desc' }"
            :per_page="10"
            endpoint="users"
            :loading="loading"
            @open-drawer="addItem"
        >
            <template #cell-photo="{ row }">
                <img 
                    v-if="row.photo"
                    :src="`${row.photo}`" 
                    alt="User Photo" 
                    class="rounded-circle"
                    style="width: 40px; height: 40px; object-fit: cover;"
                />
                <span v-else class="text-muted">No Photo</span>
            </template>
            <template #cell-active="{ row }">
                <StatusTag :status="row.active" :map="statusMap" />
            </template>
            <template #cell-action="{ row }">
                <div class="btn-group">
                    <button
                        v-if="checkPermission('master_data.users.update') && row.editable == true"
                        class="btn btn-primary btn-sm"
                        type="button"
                        @click.stop.prevent="editItem(row)"
                    >
                        <i class="fas fa-edit"></i>
                    </button>
                    <button
                        v-if="
                            checkPermission('master_data.users.delete') &&
                            row.deletable == true
                        "
                        class="btn btn-danger btn-sm"
                        type="button"
                        @click.stop.prevent="deleteItem(row.id)"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </template>
        </TableServerSide>
    </section>
    <FormAdd v-model:visible="drawer" :selected="selected" @save="handleSave" />
</template>
