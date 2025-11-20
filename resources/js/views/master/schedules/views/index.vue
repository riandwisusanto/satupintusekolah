<script setup>
import { ref, h } from 'vue'
import { usePermission } from '@/lib/permission'
import { alertError, alertSuccess, alretConfirm } from '@/lib/alert'
import { apiRequest } from '@/lib/apiClient'
import FormAdd from '../components/form.vue'


const tableRef = ref()
const { checkPermission } = usePermission()
const drawer = ref(false)
const selected = ref(null)
const loading = ref(false)

const columns = [
    { field: 'id', display: '#ID' },
    { field: 'day', display: 'Hari' },
    { field: 'teacher.name', display: 'Guru' },
    { field: 'subject.name', display: 'Mata Pelajaran' },
    { field: 'classroom.name', display: 'Kelas' },
    { field: 'start_time', display: 'Waktu Mulai' },
    { field: 'end_time', display: 'Waktu Selesai' },
    { field: 'action', display: 'Action', sortable: false },
]

const deleteItem = async (id) => {
    alretConfirm('delete').then(async (result) => {
        if (result.isConfirmed) {
            const { ok, data, error } = await apiRequest(`schedules/${id}`, {
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
            let endpoint = `schedules`
            if (row.id) {
                endpoint = `schedules/${row.id}`
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
                    <h4>Data Jadwal</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                        <li class="breadcrumb-item active">Data Jadwal</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <TableServerSide
            ref="tableRef"
            title=""
            :show-add-button="checkPermission('master_data.schedules.create')"
            :show-drawer="true"
            :columns="columns"
            :initial-sort="{ field: 'id', order: 'desc' }"
            :per_page="10"
            endpoint="schedules"
            :loading="loading"
            :extra="{
                with: 'teacher,subject,classroom',
            }"
            @open-drawer="addItem"
        >
            <template #cell-action="{ row }">
                <div class="btn-group">
                    <button
                        v-if="checkPermission('master_data.schedules.update') && row.editable == true"
                        class="btn btn-primary btn-sm"
                        type="button"
                        @click.stop.prevent="editItem(row)"
                    >
                        <i class="fas fa-edit"></i>
                    </button>
                    <button
                        v-if="
                            checkPermission('master_data.schedules.delete') &&
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
