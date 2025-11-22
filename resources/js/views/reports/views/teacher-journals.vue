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
const journals = ref([])
const summary = ref({
    total_journals: 0,
    unique_teachers: 0,
    unique_classes: 0,
    total_subjects: 0
})

const filters = ref({
    start_date: '',
    end_date: '',
    month: '',
    academic_year_id: '',
    teacher_id: '',
    subject_id: '',
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

        const { ok, data, error } = await apiRequest('reports/teacher-journals', {
            method: 'GET',
            params
        })

        if (ok) {
            journals.value = data.data.journals.data || []
            pagination.value = {
                current_page: data.data.journals.current_page,
                per_page: data.data.journals.per_page,
                total: data.data.journals.total,
                last_page: data.data.journals.last_page
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

const fetchSummary = async () => {
    try {
        const params = { ...filters.value }
        Object.keys(params).forEach(key => {
            if (!params[key]) delete params[key]
        })

        const { ok, data, error } = await apiRequest('reports/teacher-journals/summary', {
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
        subject_id: '',
        class_id: ''
    }
    journals.value = []
    summary.value = {
        total_journals: 0,
        unique_teachers: 0,
        unique_classes: 0,
        total_subjects: 0
    }
}

const goToPage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        fetchReport(page)
    }
}

const getSubjects = (journal) => {
    if (!journal.subjects || journal.subjects.length === 0) return '-'
    return journal.subjects.map(js => js.subject?.name || '-').join(', ')
}

// Computed
const hasData = computed(() => journals.value.length > 0)
const activeFilters = computed(() => {
    return Object.keys(filters.value).filter(key => filters.value[key]).length > 0
})

// Lifecycle
onMounted(() => {
    // Set default date range to current month
    const now = new Date()
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)
    
    filters.value.start_date = firstDay.toISOString().split('T')[0]
    filters.value.end_date = lastDay.toISOString().split('T')[0]
    
    applyFilters()
})
</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="font-serif-formal">Laporan Jurnal Guru</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Laporan</a></li>
                        <li class="breadcrumb-item active">Jurnal Guru</li>
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
            <div class="row mt-3">
                <div class="col-md-6">
                    <SelectServerSide
                        v-model="filters.subject_id"
                        label="Filter Mata Pelajaran (Opsional)"
                        name="subject_id"
                        placeholder="Semua Mata Pelajaran"
                        :serverside="true"
                        endpoint="subjects"
                        label-key="name"
                        value-key="id"
                    />
                </div>
                <div class="col-md-6">
                    <SelectServerSide
                        v-model="filters.class_id"
                        label="Filter Kelas (Opsional)"
                        name="class_id"
                        placeholder="Semua Kelas"
                        :serverside="true"
                        endpoint="classrooms"
                        label-key="name"
                        value-key="id"
                    />
                </div>
            </div>

            <!-- Summary Cards -->
            <div v-if="hasData" class="row mt-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ summary.total_journals }}</h3>
                            <p>Total Jurnal</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ summary.unique_teachers }}</h3>
                            <p>Jumlah Guru</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ summary.unique_classes }}</h3>
                            <p>Jumlah Kelas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-school"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ summary.total_subjects }}</h3>
                            <p>Mata Pelajaran</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book-open"></i>
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
                                Data Jurnal Guru
                            </h3>
                            <div class="card-tools">
                                <ExportButtons 
                                    v-if="hasData"
                                    :filters="filters"
                                    report-type="teacher-journals"
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
                                        <th>Nama Guru</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Tema</th>
                                        <th>Kegiatan</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in journals" :key="item.id">
                                        <td class="text-center">
                                            {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-day mr-1 text-muted"></i>
                                            {{ formatDate(item.date) }}
                                        </td>
                                        <td>
                                            <strong>{{ item.teacher?.name || '-' }}</strong>
                                        </td>
                                        <td>{{ item.classroom?.name || '-' }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ getSubjects(item) }}
                                            </span>
                                        </td>
                                        <td>{{ item.theme || '-' }}</td>
                                        <td>{{ item.activity || '-' }}</td>
                                        <td>{{ item.note || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Empty State -->
                            <div v-else class="text-center p-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
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
