<script setup>
import { ref, onMounted, computed } from 'vue'
import { alertSuccess, alertError, alretConfirm } from '@/lib/alert'
import { apiRequest } from '@/lib/apiClient'
import { formatDate } from '@/lib/formatters'
import FormSelectTeacher from '@/components/FormSelectTeacher.vue'

// State
const loading = ref(false)
const tableRef = ref(null)

// Form state
const form = ref({
    teacher_id: '',
    date: new Date().toISOString().split('T')[0],
    status: 'sick',
    notes: ''
})

const statusOptions = [
    { value: 'sick', label: 'Sakit', icon: 'fas fa-thermometer-half', color: 'warning' },
    { value: 'permission', label: 'Izin', icon: 'fas fa-file-signature', color: 'info' },
    { value: 'on_leave', label: 'Cuti', icon: 'fas fa-calendar-times', color: 'secondary' }
]

// Table columns
const columns = [
    { field: 'date', display: 'Tanggal' },
    { field: 'teacher.name', display: 'Nama Guru' },
    { field: 'status', display: 'Jenis Ketidakhadiran' },
    { field: 'note', display: 'Catatan' },
    { field: 'action', display: 'Aksi', sortable: false }
]

// Computed
const selectedStatus = computed(() => {
    return statusOptions.find(opt => opt.value === form.value.status)
})

// Methods
const resetForm = () => {
    form.value = {
        teacher_id: '',
        date: new Date().toISOString().split('T')[0],
        status: 'sick',
        notes: ''
    }
}

const handleSubmit = async () => {
    if (!form.value.teacher_id) {
        alertError('Silakan pilih guru')
        return
    }

    if (!form.value.date) {
        alertError('Silakan pilih tanggal')
        return
    }

    alretConfirm('save').then(async (result) => {
        if (result.isConfirmed) {
            await submitAbsence()
        }
    })
}

const submitAbsence = async () => {
    loading.value = true
    try {
        const { ok, data, error } = await apiRequest('teacher-attendances/sick-leave', {
            method: 'POST',
            body: form.value
        })

        if (ok) {
            alertSuccess('Data ketidakhadiran berhasil dicatat')
            resetForm()
            tableRef.value?.reload()
        } else {
            alertError(error)
        }
    } catch (err) {
        alertError(err.message)
    } finally {
        loading.value = false
    }
}

const deleteAbsence = async (id) => {
    alretConfirm('delete').then(async (result) => {
        if (result.isConfirmed) {
            const { ok, data, error } = await apiRequest(`teacher-attendances/${id}`, {
                method: 'DELETE'
            })

            if (ok) {
                alertSuccess('Data berhasil dihapus')
                tableRef.value?.reload()
            } else {
                alertError(error)
            }
        }
    })
}

const getStatusBadge = (status) => {
    const statusOption = statusOptions.find(opt => opt.value === status)
    if (!statusOption) return '<span class="badge badge-secondary">Unknown</span>'
    return `<span class="badge badge-${statusOption.color}"><i class="${statusOption.icon} mr-1"></i>${statusOption.label}</span>`
}

// Lifecycle - no need to fetch teachers, FormSelectTeacher handles it

</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="font-serif-formal">Ketidakhadiran Guru</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Absensi</a></li>
                        <li class="breadcrumb-item active">Ketidakhadiran Guru</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Form Card -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-navy shadow-sm">
                        <div class="card-header border-bottom-0">
                            <h3 class="card-title font-serif-formal">
                                <i class="fas fa-user-times mr-2"></i>
                                Catat Ketidakhadiran Guru
                            </h3>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="handleSubmit">
                                <div class="row">
                                    <!-- Teacher Selection -->
                                    <div class="col-md-6">
                                        <FormSelectTeacher
                                            v-model="form.teacher_id"
                                            label="Pilih Guru"
                                            name="teacher_id"
                                            placeholder="-- Pilih Guru --"
                                            :required="true"
                                        />
                                    </div>

                                    <!-- Date Selection -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date">
                                                <i class="fas fa-calendar mr-1"></i>
                                                Tanggal <span class="text-danger">*</span>
                                            </label>
                                            <input 
                                                id="date"
                                                v-model="form.date" 
                                                type="date" 
                                                class="form-control"
                                                required
                                            />
                                        </div>
                                    </div>

                                    <!-- Status Selection -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>
                                                <i class="fas fa-clipboard-list mr-1"></i>
                                                Jenis Ketidakhadiran <span class="text-danger">*</span>
                                            </label>
                                            <div class="status-options">
                                                <div 
                                                    v-for="option in statusOptions" 
                                                    :key="option.value"
                                                    class="status-option"
                                                    :class="{ 'active': form.status === option.value }"
                                                    @click="form.status = option.value"
                                                >
                                                    <div class="status-icon" :class="`bg-${option.color}`">
                                                        <i :class="option.icon"></i>
                                                    </div>
                                                    <div class="status-label">{{ option.label }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="notes">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                Catatan / Keterangan
                                            </label>
                                            <textarea 
                                                id="notes"
                                                v-model="form.notes" 
                                                class="form-control"
                                                rows="3"
                                                placeholder="Tambahkan catatan atau keterangan tambahan..."
                                            ></textarea>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-md-12">
                                        <button 
                                            type="submit" 
                                            class="btn btn-navy btn-lg shadow-sm"
                                            :disabled="loading"
                                        >
                                            <i class="fas fa-save mr-2"></i>
                                            {{ loading ? 'Menyimpan...' : 'Simpan Data' }}
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn btn-secondary btn-lg ml-2"
                                            @click="resetForm"
                                            :disabled="loading"
                                        >
                                            <i class="fas fa-redo mr-2"></i>
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <TableServerSide
                        ref="tableRef"
                        title="Daftar Ketidakhadiran Guru"
                        :show-add-button="false"
                        :columns="columns"
                        :initial-sort="{ field: 'date', order: 'desc' }"
                        :per_page="10"
                        endpoint="teacher-attendances"
                        :extra="{ 'filter[status][ne]': 'check_in', 'filter[status][ne]': 'check_out' }"
                    >
                        <template #cell-date="{ row }">
                            <i class="fas fa-calendar-day mr-1 text-muted"></i>
                            {{ formatDate(row.date) }}
                        </template>
                        
                        <template #cell-teacher.name="{ row }">
                            <div class="teacher-info">
                                <img 
                                    v-if="row.teacher?.photo"
                                    :src="row.teacher.photo" 
                                    alt="Teacher Photo" 
                                    class="rounded-circle mr-2"
                                    style="width: 32px; height: 32px; object-fit: cover;"
                                />
                                <span class="font-weight-bold">{{ row.teacher?.name || '-' }}</span>
                            </div>
                        </template>

                        <template #cell-status="{ row }">
                            <span v-html="getStatusBadge(row.status)"></span>
                        </template>

                        <template #cell-note="{ row }">
                            <span class="text-muted">{{ row.note || '-' }}</span>
                        </template>

                        <template #cell-action="{ row }">
                            <button
                                v-if="row.deleteable"
                                class="btn btn-danger btn-sm"
                                type="button"
                                @click.stop.prevent="deleteAbsence(row.id)"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </template>
                    </TableServerSide>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Status Options */
.status-options {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
}

.status-option {
    flex: 1;
    min-width: 150px;
    padding: 1rem;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    background: white;
}

.status-option:hover {
    border-color: #001f3f;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 31, 63, 0.15);
}

.status-option.active {
    border-color: #001f3f;
    background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
    color: white;
    box-shadow: 0 6px 16px rgba(0, 31, 63, 0.3);
}

.status-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.5rem;
    color: white;
    transition: all 0.3s ease;
}

.status-option.active .status-icon {
    background: white !important;
    color: #001f3f;
}

.status-label {
    font-weight: 600;
    font-size: 0.95rem;
}

/* Form Styling */
.select-modern {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    transition: all 0.3s ease;
}

.select-modern:focus {
    border-color: #001f3f;
    box-shadow: 0 0 0 0.2rem rgba(0, 31, 63, 0.15);
}

.form-control {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #001f3f;
    box-shadow: 0 0 0 0.2rem rgba(0, 31, 63, 0.15);
}

/* Button Styling */
.btn-navy {
    background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
    border: none;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-navy:hover {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 31, 63, 0.3);
}

.btn-secondary {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    transform: translateY(-2px);
}

/* Teacher Info */
.teacher-info {
    display: flex;
    align-items: center;
}

/* Card Styling */
.card {
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .status-option {
        min-width: 100%;
    }
    
    .btn-lg {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .btn-lg.ml-2 {
        margin-left: 0 !important;
    }
}
</style>
