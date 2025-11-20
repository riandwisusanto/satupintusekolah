<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const tableRef = ref()
const showAll = ref(false)

const columns = [
    { field: 'classroom.name', display: 'Kelas' },
    { field: 'subject.name', display: 'Mata Pelajaran' },
    { field: 'day', display: 'Hari' },
    { field: 'start_time', display: 'Jam Mulai' },
    { field: 'end_time', display: 'Jam Selesai' },
    { field: 'action', display: 'Aksi', sortable: false },
]

const toggleView = () => {
    showAll.value = !showAll.value
    // TableServerSide akan otomatis reload ketika endpoint berubah
}

const openJournalForm = (schedule) => {
    // Navigate to teacher journals with pre-filled data
    router.push({
        name: 'teacher-journals',
        query: {
            schedule_id: schedule.id,
            teacher_id: schedule.teacher_id,
            subject_id: schedule.subject_id,
            class_id: schedule.class_id,
            date: new Date().toISOString().split('T')[0]
        }
    })
}

const formatTime = (timeString) => {
    if (!timeString) return '-'
    return timeString.slice(0, 5)
}
</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4>Jadwal Mengajar</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Jadwal</a></li>
                        <li class="breadcrumb-item active">Jadwal Mengajar</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- <div class="container-fluid"> -->
            <!-- Toggle View -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <button 
                            type="button" 
                            class="btn"
                            :class="!showAll ? 'btn-primary' : 'btn-outline-primary'"
                            @click="toggleView"
                        >
                            <i class="fas fa-calendar-day"></i> Hari Ini
                        </button>
                        <button 
                            type="button" 
                            class="btn"
                            :class="showAll ? 'btn-primary' : 'btn-outline-primary'"
                            @click="toggleView"
                        >
                            <i class="fas fa-calendar-alt"></i> Semua Jadwal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Schedules Table -->
            <div class="row">
                <div class="col-12">
                    <TableServerSide
                        ref="tableRef"
                        :title="showAll ? 'Daftar Jadwal' : 'Jadwal Hari Ini'"
                        :columns="columns"
                        :endpoint="showAll ? 'schedules' : 'schedules/today'"
                        :initial-sort="{ field: 'day', order: 'asc' }"
                        :per_page="10"
                        :extra="{
                            with: 'subject,classroom',
                        }"
                    >
                        <template #cell-start_time="{ row }">
                            {{ formatTime(row.start_time) }}
                        </template>
                        <template #cell-end_time="{ row }">
                            {{ formatTime(row.end_time) }}
                        </template>
                        <template #cell-action="{ row }">
                            <button 
                                class="btn btn-sm btn-primary"
                                @click="openJournalForm(row)"
                                title="Buat Jurnal"
                            >
                                <i class="fas fa-book"></i> Jurnal
                            </button>
                        </template>
                    </TableServerSide>
                </div>
            </div>
        <!-- </div> -->
    </section>
</template>

<style scoped>
.btn-group {
    box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
}

.table-responsive {
    overflow-x: auto;
}

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125);
    margin-bottom: 1rem;
}
</style>
