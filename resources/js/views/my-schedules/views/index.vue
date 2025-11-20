<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePermission } from '@/lib/permission'
import { alertError } from '@/lib/alert'
import { apiRequest } from '@/lib/apiClient'
import dayjs from 'dayjs'

const router = useRouter()
const { checkPermission } = usePermission()
const loading = ref(false)
const schedules = ref([])
const showAll = ref(false)
const selectedSchedule = ref(null)

const fetchSchedules = async () => {
    loading.value = true
    try {
        const endpoint = showAll.value ? 'schedules/teacher' : 'schedules/teacher/today'
        const { ok, data, error } = await apiRequest(endpoint)
        
        if (ok) {
            schedules.value = data || []
        } else {
            alertError(error)
        }
    } catch (err) {
        alertError(err.message)
    } finally {
        loading.value = false
    }
}

const toggleView = () => {
    showAll.value = !showAll.value
    fetchSchedules()
}

const openJournalForm = (schedule) => {
    selectedSchedule.value = schedule
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

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return dayjs(dateString).format('DD MMMM YYYY')
}

const formatTime = (timeString) => {
    if (!timeString) return '-'
    return timeString.slice(0, 5)
}

onMounted(() => {
    fetchSchedules()
})
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
        <div class="container-fluid">
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

            <!-- Schedules List -->
            <div class="row">
                <div class="col-12">
                    <div v-if="loading" class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Memuat jadwal...
                    </div>
                    
                    <div v-else-if="schedules.length === 0" class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        {{ showAll ? 'Tidak ada jadwal untuk hari ini' : 'Tidak ada jadwal' }}
                    </div>
                    
                    <div v-else>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar"></i>
                                    {{ showAll ? 'Daftar Jadwal' : 'Jadwal Hari Ini' }}
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th v-if="!showAll">Tanggal</th>
                                                <th>Kelas</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Hari</th>
                                                <th>Jam</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(schedule, index) in schedules" :key="schedule.id">
                                                <td>{{ index + 1 }}</td>
                                                <td v-if="!showAll">{{ formatDate(new Date()) }}</td>
                                                <td>{{ schedule.class_name }}</td>
                                                <td>{{ schedule.subject_name }}</td>
                                                <td>{{ schedule.day }}</td>
                                                <td>{{ formatTime(schedule.start_time) }} - {{ formatTime(schedule.end_time) }}</td>
                                                <td>
                                                    <button 
                                                        class="btn btn-sm btn-primary"
                                                        @click="openJournalForm(schedule)"
                                                        title="Buat Jurnal"
                                                    >
                                                        <i class="fas fa-book"></i> Jurnal
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
