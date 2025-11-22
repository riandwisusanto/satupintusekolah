<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiRequest } from '@/lib/apiClient'
import { alertError } from '@/lib/alert'
import { formatDate } from '@/lib/formatters'
import FilterPanel from '../components/FilterPanel.vue'
import ExportButtons from '../components/ExportButtons.vue'
import SelectServerSide from '@/components/SelectServerSide.vue'

// State
const loading = ref(false)
const attendances = ref([])
const summary = ref({
    total_records: 0,
    total_students: 0,
    present_count: 0,
    absent_count: 0,
    sick_count: 0,
    permission_count: 0,
    attendance_percentage: 0
})

const filters = ref({
    start_date: '',
    end_date: '',
    month: '',
    academic_year_id: '',
    teacher_id: '',
    class_id: ''
})

const pagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1
})

// Methods
const fetchReport = async (page = 1) => {
    loading.value = true
    try {
        const params = {
            ...filters.value,
            page,
            per_page: pagination.value.per_page
        }
        
        // Remove empty filters
        Object.keys(params).forEach(key => {
            if (!params[key]) delete params[key]
        })

        const { ok, data, error } = await apiRequest('reports/student-attendance', {
            method: 'GET',
            params
        })

        if (ok) {
            attendances.value = data.data.attendances.data || []
            pagination.value = {
                current_page: data.data.attendances.current_page,
                per_page: data.data.attendances.per_page,
                total: data.data.attendances.total,
                last_page: data.data.attendances.last_page
            }
        } else {
            alertError(error)
        }
    } catch (err) {
        alertError(err.message)
    } finally {
        loading.value = false
    }
}

const attendanceFilters = computed(() => {
    if (!filters.value.class_id) {
        return attendances.value
    }
    return attendances.value.filter((row) => row.class_id === filters.value.class_id)
})

const fetchSummary = async () => {
    try {
        const params = { ...filters.value }
        Object.keys(params).forEach(key => {
            if (!params[key]) delete params[key]
        })

        const { ok, data, error } = await apiRequest('reports/student-attendance/summary', {
            method: 'GET',
            params
        })

        if (ok) {
            summary.value = data.data.summary
        }
    } catch (err) {
        console.error('Failed to fetch summary:', err)
    }
}

const applyFilters = async () => {
    pagination.value.current_page = 1
    await Promise.all([fetchReport(1), fetchSummary()])
}

const resetFilters = () => {
    filters.value = {
        start_date: '',
        end_date: '',
        month: '',
        academic_year_id: '',
        teacher_id: '',
        class_id: ''
    }
    attendances.value = []
    summary.value = {
        total_records: 0,
        total_students: 0,
        present_count: 0,
        absent_count: 0,
        sick_count: 0,
        permission_count: 0,
        attendance_percentage: 0
    }
}

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchReport(page)
    }
}

// Helper to calculate stats per row
const getRowStats = (details) => {
    if (!details) return { present: 0, sick: 0, permission: 0, absent: 0 }
    return {
        present: details.filter(d => d.status === 'hadir').length,
        sick: details.filter(d => d.status === 'sakit').length,
        permission: details.filter(d => d.status === 'ijin').length,
        absent: details.filter(d => d.status === 'alpa').length,
    }
}

// Computed
const hasData = computed(() => attendances.value.length > 0)
const activeFilters = computed(() => {
    return Object.keys(filters.value).filter(key => filters.value[key]).length > 0
})

// Lifecycle
onMounted(() => {
    // Set default date range to current month
    const now = new Date()
    
    filters.value.start_date = now.toISOString().split('T')[0]
    filters.value.end_date = now.toISOString().split('T')[0]
    
    applyFilters()
})
</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="font-serif-formal">Laporan Absensi Siswa</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                        <li class="breadcrumb-item active">Absensi Siswa</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Panel -->
            <FilterPanel 
                v-model="filters"
                @apply="applyFilters"
                @reset="resetFilters"
            />

            <!-- Additional Filters -->
            <!-- <div class="row mt-3">
                <div class="col-md-12">
                    <SelectServerSide
                        v-model="filters.class_id"
                        label="Filter Kelas (Opsional)"
                        name="class_id"
                        placeholder="Semua Kelas"
                        :serverside="true"
                        endpoint="classrooms?all=true"
                        label-key="name"
                        value-key="id"
                    />
                </div>
            </div> -->

            <!-- Summary Cards -->
            <div v-if="hasData" class="row mt-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ summary.total_records }}</h3>
                            <p>Total Sesi Kelas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ summary.present_count }}</h3>
                            <p>Total Hadir</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ summary.sick_count + summary.permission_count }}</h3>
                            <p>Sakit / Izin</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ summary.absent_count }}</h3>
                            <p>Tidak Hadir (Alpha)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-outline card-navy shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-serif-formal">
                                <i class="fas fa-table mr-2"></i>
                                Data Absensi Siswa
                            </h3>
                            <div class="card-tools">
                                <ExportButtons 
                                    v-if="hasData"
                                    :filters="filters"
                                    report-type="student-attendance"
                                />
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <!-- Loading State -->
                            <div v-if="loading" class="text-center p-5">
                                <i class="fas fa-spinner fa-spin fa-3x text-navy"></i>
                                <p class="mt-3">Memuat data...</p>
                            </div>

                            <!-- Data Table -->
                            <table v-else-if="hasData" class="table table-hover ledger-table text-nowrap">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">No</th>
                                        <th>Tanggal</th>
                                        <th>Kelas</th>
                                        <th>Guru Pengajar</th>
                                        <th class="text-center">Hadir</th>
                                        <th class="text-center">Sakit</th>
                                        <th class="text-center">Izin</th>
                                        <th class="text-center">Alpha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in attendanceFilters" :key="item.id">
                                        <td class="text-center">
                                            {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-day mr-1 text-muted"></i>
                                            {{ formatDate(item.date) }}
                                        </td>
                                        <td>
                                            <strong>{{ item.classroom?.name || '-' }}</strong>
                                        </td>
                                        <td>{{ item.teacher?.name || '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-success">{{ getRowStats(item.details).present }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-warning">{{ getRowStats(item.details).sick }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ getRowStats(item.details).permission }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-danger">{{ getRowStats(item.details).absent }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Empty State -->
                            <div v-else class="text-center p-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"> Data Kosong</i>
                                <p class="text-muted">
                                    {{ activeFilters ? 'Tidak ada data untuk filter yang dipilih' : 'Silakan pilih filter dan klik "Tampilkan Laporan"' }}
                                </p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="hasData && pagination.last_page > 1" class="card-footer clearfix">
                            <ul class="pagination pagination-sm m-0 float-right">
                                <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                    <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page - 1)">«</a>
                                </li>
                                <li 
                                    v-for="page in pagination.last_page" 
                                    :key="page"
                                    class="page-item"
                                    :class="{ active: pagination.current_page === page }"
                                >
                                    <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
                                </li>
                                <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                    <a class="page-link" href="#" @click.prevent="goToPage(pagination.current_page + 1)">»</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.small-box {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.small-box .inner {
    padding: 15px;
}

.small-box .icon {
    font-size: 70px;
    opacity: 0.3;
}

.badge {
    padding: 5px 10px;
    font-size: 11px;
    min-width: 30px;
}

.table th {
    background-color: #001f3f;
    color: white;
    font-weight: 600;
}

.text-navy {
    color: #001f3f;
}
</style>
