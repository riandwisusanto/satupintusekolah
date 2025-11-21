<script setup>
import { ref, h } from 'vue'
import { usePermission } from '@/lib/permission'
import { alertError, alertSuccess, alretConfirm } from '@/lib/alert'
import { apiRequest } from '@/lib/apiClient'
import MultiSubjectJournal from '../components/MultiSubjectJournal.vue'
import { formatDate } from '../../../lib/formatters'


const tableRef = ref()
const { checkPermission } = usePermission()
const showJournalForm = ref(false)
const loading = ref(false)

const statusMap = {
    1: { text: 'Aktif', class: 'badge-success' },
    0: { text: 'Tidak Aktif', class: 'badge-danger' },
}

const columns = [
    { 
        field: 'date', 
        display: 'Tanggal', 
        component: (row) => h('span', {}, formatDate(row.row.date)) 
    },
    { field: 'theme', display: 'Tema' },
    { field: 'activity', display: 'Aktivitas' },
    { field: 'notes', display: 'Catatan' },
    { 
        field: 'subjects', 
        display: 'Mata Pelajaran', 
        component: (row) => h('span', {}, row.row.subjects.map((subject) => subject.subject.name).join(', ')) 
    },
    { field: 'classroom.name', display: 'Kelas' },
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

const closeJournalForm = () => {
    showJournalForm.value = false
}

const refreshDashboard = () => {
    tableRef.value?.reload()
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
            endpoint="journals"
            :loading="loading"
            :extra="{
                with: 'classroom,subjects.subject',
            }"
            @open-drawer="showJournalForm = true"
        >
            <template #cell-action="{ row }">
                <div class="btn-group">
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
    <MultiSubjectJournal 
        v-if="showJournalForm" 
        :visible="showJournalForm" 
        @close="closeJournalForm"
        @success="refreshDashboard"
    />
</template>

<style scoped>
.text-red-500 {
    color: #ef4444;
}
</style>
