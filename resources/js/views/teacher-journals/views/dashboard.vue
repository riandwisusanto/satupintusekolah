<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiRequest } from '@/lib/apiClient'
import { useUser } from '@/store'
import { formatDate, formatTime } from '@/lib/formatters'
import MultiSubjectJournal from '../components/MultiSubjectJournal.vue'
import { useRouter } from 'vue-router'

// State
const loading = ref(false)
const dashboardData = ref(null)
const showJournalForm = ref(false)
const { user } = useUser()
const router = useRouter()

// Computed properties
const isHomeroomTeacher = computed(() => dashboardData.value?.user?.is_homeroom_teacher || false)
const hasSubmittedToday = computed(() => dashboardData.value?.has_submitted_today || false)
const todaySubjects = computed(() => dashboardData.value?.today_subjects || [])
const todaySchedule = computed(() => dashboardData.value?.today_schedule || [])
const pendingDays = computed(() => dashboardData.value?.pending_days || [])
const stats = computed(() => dashboardData.value?.stats || {})

// Format time for display
const formatScheduleTime = (startTime, endTime) => {
    return `${formatTime(startTime)} - ${formatTime(endTime)}`
}

// Fetch dashboard data
const fetchDashboardData = async () => {
    loading.value = true
    try {
        const { ok, data } = await apiRequest('journals/teacher-dashboard')
        if (ok) {
            dashboardData.value = data.data
        }
    } catch (err) {
        console.error('Error fetching dashboard data:', err)
    } finally {
        loading.value = false
    }
}

// Open journal form
const openJournalForm = () => {
    showJournalForm.value = true
}

// Close journal form
const closeJournalForm = () => {
    showJournalForm.value = false
}

// Refresh dashboard data
const refreshDashboard = () => {
    fetchDashboardData()
}

// Lifecycle
onMounted(() => {
    fetchDashboardData()
})

const quickAction = (action) => {
    if (action === 'schedule') {
        router.push({ name: 'my-schedules' })
    }
}
</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4>Dashboard</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Memuat data dashboard...</p>
            </div>

            <!-- Dashboard Content -->
            <div v-else-if="dashboardData">
                <!-- User Welcome Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-gradient-primary">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h3 class="mb-1">
                                            Selamat Datang, {{ dashboardData.user.name }}!
                                        </h3>
                                        <p class="mb-0">
                                            <span v-if="isHomeroomTeacher" class="badge badge-info mr-2">
                                                <i class="fas fa-chalkboard-teacher mr-1"></i>
                                                Wali Kelas {{ dashboardData.user.homeroom_class?.name }}
                                            </span>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ hasSubmittedToday ? 'Jurnal Hari Ini Sudah Diisi' : 'Belum Input Jurnal Hari Ini' }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <button 
                                            @click="refreshDashboard" 
                                            class="btn btn-outline-light btn-sm"
                                            title="Refresh Dashboard"
                                        >
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ todaySchedule.length }}</h3>
                                <p>Jadwal Hari Ini</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ stats.this_month_journals || 0 }}</h3>
                                <p>Jurnal Bulan Ini</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ pendingDays.length }}</h3>
                                <p>Jurnal Belum Selesai</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ stats.completion_rate || 0 }}%</h3>
                                <p>Tingkat Penyelesaian</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-bolt"></i>
                                    Aksi Cepat
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Multi-Subject Journal (Hanya untuk Guru Kelas) -->
                                    <div v-if="isHomeroomTeacher" class="col-md-3 col-sm-6 mb-3">
                                        <button 
                                            @click="openJournalForm" 
                                            class="btn btn-lg btn-success btn-block"
                                            :disabled="hasSubmittedToday"
                                        >
                                            <i class="fas fa-pen-fancy fa-2x"></i><br>
                                            Input Jurnal<br>
                                            <small>{{ todaySubjects.length }} Mata Pelajaran Hari Ini</small>
                                        </button>
                                    </div>
                                    
                                    <!-- View Schedule -->
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <button class="btn btn-lg btn-info btn-block">
                                            <i class="fas fa-calendar-alt fa-2x mb-2"></i><br>
                                            Lihat Jadwal<br>
                                            <small>{{ todaySchedule.length }} Kelas Hari Ini</small>
                                        </button>
                                    </div>
                                    
                                    <!-- Attendance -->
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <a href="/student-attendance" class="btn btn-lg btn-warning btn-block">
                                            <i class="fas fa-user-check fa-2x mb-2"></i><br>
                                            Absen Siswa<br>
                                            <small>{{ isHomeroomTeacher ? dashboardData.user.homeroom_class?.name : 'Semua Kelas' }}</small>
                                        </a>
                                    </div>
                                    
                                    <!-- Journal History -->
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <button class="btn btn-lg btn-primary btn-block">
                                            <i class="fas fa-history fa-2x"></i><br>
                                            Riwayat Jurnal<br>
                                            <small>Lihat Semua Jurnal</small>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Schedule -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clock"></i>
                                    Jadwal Hari Ini
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div v-if="todaySchedule.length === 0" class="alert alert-info m-3">
                                    <i class="fas fa-info-circle"></i>
                                    Tidak ada jadwal untuk hari ini.
                                </div>
                                
                                <div v-else class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Waktu</th>
                                                <th>Mata Pelajaran</th>
                                                <th>Kelas</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="schedule in todaySchedule" :key="schedule.id">
                                                <td>
                                                    <span class="badge badge-secondary">
                                                        {{ formatScheduleTime(schedule.start_time, schedule.end_time) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{{ schedule.subject.name }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ schedule.classroom.name }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> Jadwal Aktif
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Journals -->
                <div v-if="pendingDays.length > 0" class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-warning">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Jurnal Belum Selesai (7 Hari Terakhir)
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div v-for="day in pendingDays" :key="day.date" class="col-md-2 col-sm-4 col-6 mb-2">
                                        <div class="btn btn-warning btn-block">
                                            <i class="fas fa-calendar-day"></i>
                                            {{ day.day_name }}<br>
                                            <small>{{ day.day_date }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Multi-Subject Journal Modal -->
    <MultiSubjectJournal 
        v-if="showJournalForm" 
        :visible="showJournalForm" 
        @close="closeJournalForm"
        @success="refreshDashboard"
    />
</template>

<style scoped>
.small-box {
    border-radius: 0.5rem;
    transition: transform 0.2s;
}

.small-box:hover {
    transform: translateY(-2px);
}

.btn-lg {
    height: 120px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    text-align: center;
}

.btn-lg i {
    color: rgba(255, 255, 255, 0.8);
}

.btn-lg:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.badge {
    font-size: 0.75rem;
}

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

@media (max-width: 768px) {
    .btn-lg {
        height: 100px;
        font-size: 0.875rem;
    }
    
    .btn-lg i {
        font-size: 1.5rem;
    }
}
</style>
