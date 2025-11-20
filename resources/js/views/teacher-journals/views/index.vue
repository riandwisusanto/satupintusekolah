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

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID')
}

const columns = [
    { field: 'id', display: '#ID' },
    { field: 'date', display: 'Tanggal' },
    { field: 'theme', display: 'Tema' },
    { field: 'activity', display: 'Aktivitas' },
    { field: 'teacher.name', display: 'Guru' },
    { field: 'subject.name', display: 'Mata Pelajaran' },
    { field: 'classroom.name', display: 'Kelas' },
    { field: 'active', display: 'Status' },
    { field: 'action', display: 'Action', sortable: false },
]

const deleteItem = async (id) => {
    alretConfirm('delete').then(async (result) => {
        if (result.isConfirmed) {
            const { ok, data, error } = await apiRequest(`teacher-journals/${id}`, {
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

const save = async (row) => {
    alretConfirm('save').then(async (result) => {
        if (result.isConfirmed) {
            loading.value = true
            let endpoint = `teacher-journals`
            if (row.id) {
                endpoint = `teacher-journals/${row.id}`
            }
            const { ok, data, error } = await apiRequest(endpoint, {
                method: row.id ? 'put' : 'post',
                body: row,
            })

            if (ok) {
                alertSuccess(data.message)

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
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4>Jurnal Guru</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Jurnal</a></li>
                        <li class="breadcrumb-item active">Jurnal Guru</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <TableServerSide
            ref="tableRef"
            title=""
            :show-add-button="checkPermission('teacher_journals.create')"
            :show-drawer="true"
            :columns="columns"
            :initial-sort="{ field: 'id', order: 'desc' }"
            :per_page="10"
            endpoint="teacher-journals"
            :loading="loading"
            @open-drawer="addItem"
        >
            <template #cell-date="{ row }">
                {{ formatDate(row.date) }}
            </template>
            <template #cell-active="{ row }">
                <StatusTag :status="row.active" :map="statusMap" />
            </template>
            <template #cell-action="{ row }">
                <div class="btn-group">
                    <button
                        v-if="checkPermission('teacher_journals.update') && row.editable == true"
                        class="btn btn-primary btn-sm"
                        type="button"
                        @click.stop.prevent="editItem(row)"
                    >
                        <i class="fas fa-edit"></i>
                    </button>
                    <button
                        v-if="
                            checkPermission('teacher_journals.delete') &&
                            row.deleteable == true
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
    <FormAdd v-model:visible="drawer" :selected="selected" @save="save" />
</template>

<style scoped>
.text-red-500 {
    color: #ef4444;
}
</style>
