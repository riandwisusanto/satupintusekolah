<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiRequest } from '@/lib/apiClient'
import { alertSuccess, alertError } from '@/lib/alert'
import { useUser } from '@/store'

// State
const loading = ref(false)
const saving = ref(false)
const selectedClass = ref(null)
const selectedDate = ref(new Date().toISOString().split('T')[0])
const todaySchedules = ref([])
const students = ref([])
const { user } = useUser()

// Status options
const statusOptions = [
    { value: 'hadir', label: 'Hadir', color: 'success' },
    { value: 'sakit', label: 'Sakit', color: 'warning' },
    { value: 'izin', label: 'Izin', color: 'info' },
    { value: 'alpa', label: 'Alpa', color: 'danger' }
]

// Computed
const isHomeroomTeacher = computed(() => {
    // Check if user has homeroom class from today's schedules
    const homeroomSchedules = todaySchedules.value.filter(s => s.is_homeroom)
    return homeroomSchedules.length > 0
})

const classOptions = computed(() => {
    // Group schedules by class for homeroom teachers
    if (isHomeroomTeacher.value) {
        const classMap = new Map()
        todaySchedules.value.forEach(schedule => {
            if (!schedule.classroom) return
            
            if (!classMap.has(schedule.classroom.id)) {
                classMap.set(schedule.classroom.id, {
                    id: schedule.classroom.id,
                    name: schedule.classroom.name,
                    subjects: []
                })
            }
            if (schedule.subject) {
                classMap.get(schedule.classroom.id).subjects.push(schedule.subject)
            }
        })
        return Array.from(classMap.values())
    }
    
    // Return individual schedules for subject teachers
    return todaySchedules.value.map(schedule => ({
        id: schedule.id,
        value: schedule.id,
        name: `${schedule.classroom?.name || 'Unknown Class'} - ${schedule.subject?.name || 'Unknown Subject'}`,
        label: `${schedule.classroom?.name || 'Unknown Class'} - ${schedule.subject?.name || 'Unknown Subject'}`,
        classId: schedule.classroom?.id,
        subjectId: schedule.subject?.id,
        time: `${schedule.start_time} - ${schedule.end_time}`
    }))
})

const selectedSchedule = computed(() => {
    if (isHomeroomTeacher.value) {
        return classOptions.value.find(c => c.id === selectedClass.value)
    }
    return classOptions.value.find(s => s.id === selectedClass.value)
})

// Fetch today's schedules
const fetchTodaySchedules = async () => {
    try {
        const { ok, data } = await apiRequest('schedules/today')
        if (ok) {
            todaySchedules.value = data.data || []
            
            // Auto-select first option if none selected
            if (!selectedClass.value && classOptions.value.length > 0) {
                selectedClass.value = classOptions.value[0].id
                await fetchStudentsData()
            }
        }
    } catch (err) {
        console.error('Error fetching today schedules:', err)
    }
}

// Fetch students data
const fetchStudentsData = async () => {
    if (!selectedClass.value) return

    loading.value = true
    try {
        const classId = isHomeroomTeacher.value 
            ? selectedClass.value 
            : selectedSchedule.value?.classId

        if (!classId) return

        const { ok, data } = await apiRequest(`students?class_id=${classId}`)
        if (ok) {
            students.value = (data.data || []).map(student => ({
                ...student,
                status: 'hadir', // Default status
                note: ''
            }))
        }
    } catch (err) {
        console.error('Error fetching students:', err)
    } finally {
        loading.value = false
    }
}

// Change selection
const changeSelection = () => {
    fetchStudentsData()
}

// Update student status
const updateStudentStatus = (studentId, status) => {
    const student = students.value.find(s => s.id === studentId)
    if (student) {
        student.status = status
    }
}

// Update student note
const updateStudentNote = (studentId, note) => {
    const student = students.value.find(s => s.id === studentId)
    if (student) {
        student.note = note
    }
}

// Save attendance
const saveAttendance = async () => {
    if (!selectedClass.value || students.value.length === 0) return

    saving.value = true
    try {
        let attendancePayload = {
            date: selectedDate.value,
            class_id: selectedClass.value,
            details: students.value.map(student => ({
                student_id: student.id,
                status: student.status,
                note: student.note || null
            }))
        }

        if (isHomeroomTeacher.value) {
            // Multi-subject save for homeroom teachers
            const classData = classOptions.value.find(c => c.id === selectedClass.value)
            attendancePayload.subjects = classData.subjects.map(subject => ({
                subject_id: subject.id
            }))
        } else {
            // Single-subject save for subject teachers
            const schedule = selectedSchedule.value
            attendancePayload.class_id = schedule.classId // Ensure class_id is correct
            attendancePayload.subjects = [{
                subject_id: schedule.subjectId
            }]
        }

        const { ok, data, error } = await apiRequest('student-attendances', {
            method: 'post',
            body: attendancePayload
        })

        if (!ok) {
            throw new Error(error || 'Gagal menyimpan absensi')
        }

        alertSuccess(isHomeroomTeacher.value 
            ? `Absensi berhasil disimpan untuk ${attendancePayload.subjects.length} mata pelajaran`
            : 'Absensi berhasil disimpan')
        
        await fetchStudentsData() // Refresh data
    } catch (err) {
        console.error('Error saving attendance:', err)
        alertError(err.message || 'Terjadi kesalahan saat menyimpan absensi')
    } finally {
        saving.value = false
    }
}

// Select all students with specific status
const selectAllWithStatus = (status) => {
    students.value.forEach(student => {
        student.status = status
    })
}

// Clear all notes
const clearAllNotes = () => {
    students.value.forEach(student => {
        student.note = ''
    })
}

// Get status badge class
const getStatusBadgeClass = (status) => {
    const statusOption = statusOptions.find(opt => opt.value === status)
    return statusOption ? `badge-${statusOption.color}` : 'badge-secondary'
}

// Lifecycle
onMounted(() => {
    fetchTodaySchedules()
})
</script>

<template>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark" style="font-family: 'Georgia', serif; font-weight: bold;">
                        <i class="fas fa-book-reader mr-2"></i>Buku Absensi Siswa
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Beranda</a></li>
                        <li class="breadcrumb-item active">Absensi Siswa</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-navy shadow-sm">
                <div class="card-header bg-white p-4 border-bottom-0">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="text-muted text-uppercase font-weight-bold small">Tanggal</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                    <input 
                                        v-model="selectedDate" 
                                        type="date" 
                                        class="form-control border-left-0"
                                        @change="changeSelection"
                                        :max="new Date().toISOString().split('T')[0]"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="text-muted text-uppercase font-weight-bold small">Kelas / Mata Pelajaran</label>
                                <div class="input-group select-input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-chalkboard-teacher"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <SelectServerSide
                                            v-model="selectedClass"
                                            :options="classOptions"
                                            :serverside="false"
                                            @select="changeSelection"
                                            class="custom-select-container"
                                            wrapper-class=""
                                            teleport="body"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="d-none d-md-block small">&nbsp;</label>
                                <button 
                                    @click="saveAttendance" 
                                    class="btn btn-navy btn-block shadow-sm"
                                    :disabled="saving || !selectedClass || students.length === 0"
                                >
                                    <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                                    <i v-else class="fas fa-save mr-2"></i>
                                    {{ saving ? 'Menyimpan...' : 'Simpan Kehadiran' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="card-body border-top bg-light py-2" v-if="students.length > 0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="fas fa-info-circle mr-1"></i>
                            Total: <strong>{{ students.length }}</strong> Siswa
                            <span v-if="hasExistingAttendance" class="ml-2 badge badge-warning">Mode Edit</span>
                        </span>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-check-double mr-1"></i> Set Semua
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" @click.prevent="selectAllWithStatus('hadir')">
                                    <i class="fas fa-check text-success mr-2"></i> Hadir
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="selectAllWithStatus('sakit')">
                                    <i class="fas fa-thermometer text-warning mr-2"></i> Sakit
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="selectAllWithStatus('izin')">
                                    <i class="fas fa-file-alt text-info mr-2"></i> Izin
                                </a>
                                <a class="dropdown-item" href="#" @click.prevent="selectAllWithStatus('alpa')">
                                    <i class="fas fa-times text-danger mr-2"></i> Alpa
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" @click.prevent="clearAllNotes()">
                                    <i class="fas fa-eraser text-secondary mr-2"></i> Hapus Semua Catatan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card-body p-0">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-navy" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Membuka buku absensi...</p>
                    </div>

                    <div v-else-if="students.length > 0" class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0 ledger-table">
                            <thead class="bg-navy text-white">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th>Nama Siswa</th>
                                    <th width="200" class="text-center">Status Kehadiran</th>
                                    <th width="350">Catatan Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(student, index) in students" :key="student.id">
                                    <td class="text-center align-middle font-weight-bold text-muted">{{ index + 1 }}</td>
                                    <td class="align-middle">
                                        <div class="d-flex flex-column">
                                            <span class="student-name">{{ student.name }}</span>
                                            <small class="text-muted">NIS: {{ student.nis || '-' }}</small>
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                            <label 
                                                :class="['btn btn-sm btn-outline-success', { active: student.status === 'hadir' }]"
                                                @click="updateStudentStatus(student.id, 'hadir')"
                                                title="Hadir"
                                            >
                                                <input type="radio" name="options" autocomplete="off" :checked="student.status === 'hadir'"> H
                                            </label>
                                            <label 
                                                :class="['btn btn-sm btn-outline-warning', { active: student.status === 'sakit' }]"
                                                @click="updateStudentStatus(student.id, 'sakit')"
                                                title="Sakit"
                                            >
                                                <input type="radio" name="options" autocomplete="off" :checked="student.status === 'sakit'"> S
                                            </label>
                                            <label 
                                                :class="['btn btn-sm btn-outline-info', { active: student.status === 'izin' }]"
                                                @click="updateStudentStatus(student.id, 'izin')"
                                                title="Izin"
                                            >
                                                <input type="radio" name="options" autocomplete="off" :checked="student.status === 'izin'"> I
                                            </label>
                                            <label 
                                                :class="['btn btn-sm btn-outline-danger', { active: student.status === 'alpa' }]"
                                                @click="updateStudentStatus(student.id, 'alpa')"
                                                title="Alpa"
                                            >
                                                <input type="radio" name="options" autocomplete="off" :checked="student.status === 'alpa'"> A
                                            </label>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <input 
                                            v-model="student.note" 
                                            type="text" 
                                            class="form-control form-control-sm border-0 bg-transparent"
                                            placeholder="Tulis catatan..."
                                            style="border-bottom: 1px dashed #ccc !important;"
                                            @input="updateStudentNote(student.id, $event.target.value)"
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else-if="!loading" class="text-center py-5 text-muted">
                        <i class="fas fa-book-open fa-3x mb-3 opacity-50"></i>
                        <p class="lead" v-if="!selectedClass">Silakan pilih kelas untuk membuka buku absensi.</p>
                        <p class="lead" v-else>Tidak ada siswa terdaftar di kelas ini.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.card-navy.card-outline {
    border-top: 3px solid #001f3f;
}

.btn-navy {
    background-color: #001f3f;
    color: white;
    border-color: #001f3f;
}

.btn-navy:hover {
    background-color: #001933;
    color: white;
}

.bg-navy {
    background-color: #001f3f !important;
}

.text-navy {
    color: #001f3f !important;
}

.ledger-table {
    border: 1px solid #dee2e6;
}

.ledger-table thead th {
    border-bottom: 2px solid #001f3f;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.student-name {
    font-family: 'Georgia', serif;
    font-size: 1.05rem;
    color: #333;
}

.btn-group-toggle .btn {
    font-weight: bold;
    width: 25%;
}

.btn-outline-success:not(:disabled):not(.disabled).active, 
.btn-outline-success:not(:disabled):not(.disabled):active, 
.show>.btn-outline-success.dropdown-toggle {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-outline-warning:not(:disabled):not(.disabled).active, 
.btn-outline-warning:not(:disabled):not(.disabled):active, 
.show>.btn-outline-warning.dropdown-toggle {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-outline-info:not(:disabled):not(.disabled).active, 
.btn-outline-info:not(:disabled):not(.disabled):active, 
.show>.btn-outline-info.dropdown-toggle {
    background-color: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

.btn-outline-danger:not(:disabled):not(.disabled).active, 
.btn-outline-danger:not(:disabled):not(.disabled):active, 
.show>.btn-outline-danger.dropdown-toggle {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

/* Input styling */
.input-group-text {
    background-color: #f4f6f9;
    border-color: #ced4da;
    color: #495057;
}

.form-control:focus {
    border-color: #001f3f;
    box-shadow: none;
}

/* Smooth transitions */
.btn, .form-control {
    transition: all 0.2s ease-in-out;
}

/* Custom Select Styling to match Input Group */
:deep(.custom-select-container .vue-select) {
    border: 1px solid #ced4da !important;
    border-left: 0 !important;
    border-radius: 0 0.25rem 0.25rem 0 !important;
    min-height: 38px; /* Match standard input height */
    background-color: #fff;
}

/* Target the inner control div if it exists (depending on library version) */
:deep(.custom-select-container .vue-select .control) {
    border: none !important;
    box-shadow: none !important;
    background-color: transparent !important;
    border-radius: 0 !important;
    min-height: 36px;
}

:deep(.custom-select-container .vue-select:hover) {
    border-color: #b8c2cc !important;
}

:deep(.custom-select-container .vue-select.active),
:deep(.custom-select-container .vue-select.focused) {
    border-color: #001f3f !important;
    box-shadow: none !important;
    outline: none !important;
}

/* Hide the default label inside SelectServerSide since we use our own */
:deep(.custom-select-container .form-label) {
    display: none;
}

.select-input-group {
    flex-wrap: nowrap !important;
}

/* Adjust input group for flex child to mimic .form-control behavior */
.select-input-group .flex-grow-1 {
    min-width: 0;
    display: flex;
    flex: 1 1 auto;
    width: 1%; /* Critical for input-group flex layout to work correctly and prevent wrapping */
}

.custom-select-container {
    width: 100%;
}

/* Fix dropdown overflow/z-index issue */
:deep(.custom-select-container .vue-select .menu) {
    z-index: 9999 !important;
    position: absolute !important;
}

:deep(.input-group) {
    z-index: 10; /* Ensure input group is above other elements */
    position: relative;
}
</style>
