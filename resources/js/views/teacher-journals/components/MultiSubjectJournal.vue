<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { apiRequest } from '@/lib/apiClient'
import { alertSuccess, alertError } from '@/lib/alert'
import { formatDate } from '@/lib/formatters'
import { useUser } from '../../../store'

// Props
const props = defineProps({
    visible: {
        type: Boolean,
        required: true
    }
})

const {user} = useUser()

// Emits
const emit = defineEmits(['close', 'success'])

// State
const loading = ref(false)
const todaySubjects = ref([])
const selectedSubjects = ref([])
const journalForm = ref({
    date: new Date().toISOString().split('T')[0],
    theme: '',
    activity: '',
    notes: ''
})

// Computed
const isSubmitDisabled = computed(() => {
    return selectedSubjects.value.length === 0 || 
           !journalForm.value.theme.trim() || 
           !journalForm.value.activity.trim() ||
           loading.value
})

// Fetch today's subjects
const fetchTodaySubjects = async () => {
    try {
        const { ok, data } = await apiRequest('journals/today-subjects')
        if (ok) {
            todaySubjects.value = data.data?.subjects || []
        }
    } catch (err) {
        console.error('Error fetching today subjects:', err)
    }
}

// Toggle subject selection
const toggleSubject = (subjectId) => {
    const index = selectedSubjects.value.indexOf(subjectId)
    if (index > -1) {
        selectedSubjects.value.splice(index, 1)
    } else {
        selectedSubjects.value.push(subjectId)
    }
}

// Check if subject is selected
const isSubjectSelected = (subjectId) => {
    return selectedSubjects.value.includes(subjectId)
}

// Select all subjects
const selectAllSubjects = () => {
    selectedSubjects.value = todaySubjects.value.map(subject => subject.id)
}

// Clear selection
const clearSelection = () => {
    selectedSubjects.value = []
}

// Submit journal
const submitJournal = async () => {
    if (isSubmitDisabled.value) return

    loading.value = true
    try {
        const journalData = {
            ...journalForm.value,
            subject_ids: selectedSubjects.value,
            teacher_id: user.user.id,
            class_id: todaySubjects.value.find(subject => subject.id === selectedSubjects.value[0])?.class_id,
            active: true
        }

        const { ok, data, error } = await apiRequest('journals', {
            method: 'post',
            body: journalData
        })

        if (ok) {
            alertSuccess(data.message)
            emit('success')
            closeForm()
        } else {
            alertError(error)
        }
    } catch (err) {
        console.error('Error submitting journal:', err)
        alertError('Terjadi kesalahan saat menyimpan jurnal')
    } finally {
        loading.value = false
    }
}

// Close form
const closeForm = () => {
    emit('close')
}

// Reset form
const resetForm = () => {
    journalForm.value = {
        date: new Date().toISOString().split('T')[0],
        theme: '',
        activity: '',
        notes: ''
    }
    selectedSubjects.value = []
}

// Lifecycle
onMounted(() => {
    if (props.visible) {
        fetchTodaySubjects()
    }
})

// Watch for visibility changes
watch(() => props.visible, (newVal) => {
    if (newVal) {
        fetchTodaySubjects()
    } else {
        resetForm()
    }
})
</script>

<template>
    <div v-if="visible" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fas fa-pen-fancy"></i>
                        Input Jurnal
                    </h4>
                    <button type="button" @click="closeForm" class="close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitJournal">
                        <!-- Date Selection -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Tanggal
                                </label>
                                <input 
                                    v-model="journalForm.date" 
                                    type="date" 
                                    class="form-control"
                                    :max="new Date().toISOString().split('T')[0]"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Subject Selection -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-book"></i> Mata Pelajaran Hari Ini
                                    <span class="badge badge-info ml-2">{{ selectedSubjects.length }} Dipilih</span>
                                </label>
                                
                                <!-- Quick Actions -->
                                <div class="mb-2">
                                    <button 
                                        type="button" 
                                        @click="selectAllSubjects" 
                                        class="btn btn-sm btn-outline-primary mr-2"
                                    >
                                        <i class="fas fa-check-square"></i> Pilih Semua
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="clearSelection" 
                                        class="btn btn-sm btn-outline-secondary"
                                    >
                                        <i class="fas fa-times-square"></i> Hapus Pilihan
                                    </button>
                                </div>

                                <!-- Subject Grid -->
                                <div v-if="todaySubjects.length > 0" class="row">
                                    <div 
                                        v-for="subject in todaySubjects" 
                                        :key="subject.id" 
                                        class="col-md-6 mb-2"
                                    >
                                        <div 
                                            @click="toggleSubject(subject.id)"
                                            class="card subject-card"
                                            :class="{ 'selected': isSubjectSelected(subject.id) }"
                                        >
                                            <div class="card-body py-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check mr-3">
                                                        <input 
                                                            type="checkbox" 
                                                            :checked="isSubjectSelected(subject.id)"
                                                            @change="toggleSubject(subject.id)"
                                                            class="form-check-input"
                                                        >
                                                    </div>
                                                    <div>
                                                        <strong>{{ subject.name }}</strong>
                                                        <br>
                                                        <small class="text-muted">Kelas {{ subject.class_name }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Tidak ada mata pelajaran untuk hari ini.
                                </div>
                            </div>
                        </div>

                        <!-- Journal Content -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-lightbulb"></i> Tema Pembelajaran
                                </label>
                                <textarea 
                                    v-model="journalForm.theme" 
                                    class="form-control"
                                    rows="2"
                                    placeholder="Contoh: Bilangan Bulat dan Pecahan"
                                    required
                                ></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-tasks"></i> Kegiatan Pembelajaran
                                </label>
                                <textarea 
                                    v-model="journalForm.activity" 
                                    class="form-control"
                                    rows="4"
                                    placeholder="Deskripsikan kegiatan pembelajaran yang dilakukan..."
                                    required
                                ></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-sticky-note"></i> Catatan Tambahan
                                </label>
                                <textarea 
                                    v-model="journalForm.notes" 
                                    class="form-control"
                                    rows="3"
                                    placeholder="Catatan khusus, kendala, atau hal lain yang perlu dicatat..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong> Jurnal ini akan tersimpan untuk {{ selectedSubjects.length }} mata pelajaran yang dipilih dengan konten yang sama.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button 
                        type="button" 
                        @click="closeForm" 
                        class="btn btn-default"
                        :disabled="loading"
                    >
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button 
                        type="button" 
                        @click="submitJournal" 
                        class="btn btn-success"
                        :disabled="isSubmitDisabled"
                    >
                        <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fas fa-save mr-2"></i>
                        {{ loading ? 'Menyimpan...' : 'Simpan Jurnal' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.subject-card {
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid #dee2e6;
}

.subject-card:hover {
    border-color: #007bff;
    transform: translateY(-1px);
}

.subject-card.selected {
    border-color: #28a745;
    background-color: #d4edda;
}

.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;
    width: 100%;
    height: 100%;
    overflow: hidden;
    outline: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px 20px 280px;
    box-sizing: border-box;
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 0;
    max-width: 90%;
    max-height: 90vh;
    overflow: auto;
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 0.3rem;
    outline: 0;
}

.modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 1rem 1rem;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: calc(0.3rem - 1px);
    border-top-right-radius: calc(0.3rem - 1px);
}

.modal-title {
    margin-bottom: 0;
    line-height: 1.5;
}

.modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 1rem;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
}

.close {
    float: right;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.close:hover {
    color: #000;
    text-decoration: none;
    opacity: 0.75;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .modal {
        padding: 60px 10px 10px 10px;
    }
    
    .modal-dialog {
        max-width: 95%;
        margin: 0;
    }
    
    .subject-card {
        margin-bottom: 1rem;
    }
}
</style>
