<template>
    <FormDrawer
        v-model="visible"
        :title="selected ? 'Ubah Jurnal Guru' : 'Tambah Jurnal Guru'"
        width="600"
        @save="saveItem"
    >
        <!-- Hidden auto-filled fields -->
        <FormInput 
            v-model="form.teacher_id" 
            type="hidden" 
        />
        <FormInput 
            v-model="form.class_id" 
            type="hidden" 
        />
        
        <!-- Schedule Selector -->
        <SelectServerSide
            v-model="selectedScheduleId"
            label="Jadwal"
            placeholder="Pilih Jadwal"
            :required="true"
            :options="todaySchedules"
            :serverside="false"
            @select="onScheduleChange"
        />
        
        <!-- Multi-Select Subjects -->
        <SelectServerSide
            v-model="selectedScheduleId"
            label="Mata Pelajaran"
            placeholder="Pilih Mata Pelajaran"
            :required="true"
            :options="subjects"
            :serverside="false"
        />
        
        <FormInput 
            v-model="form.date" 
            label="Tanggal" 
            type="date" 
            required 
        />
        
        <FormInput 
            v-model="form.theme" 
            label="Tema" 
            required 
            placeholder="Masukkan tema pembelajaran"
        />
        
        <div class="form-group">
            <label>Aktivitas<span class="text-red-500">*</span></label>
            <textarea
                v-model="form.activity"
                class="form-control"
                rows="4"
                required
                placeholder="Deskripsikan aktivitas yang dilakukan"
            ></textarea>
        </div>
        
        <div class="form-group">
            <label>Catatan</label>
            <textarea
                v-model="form.notes"
                class="form-control"
                rows="3"
                placeholder="Catatan tambahan (opsional)"
            ></textarea>
        </div>
        
        <FormToggleStatus v-model="form.active" />
    </FormDrawer>
</template>

<script setup>
import { computed, reactive, watch, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { apiRequest } from '@/lib/apiClient'

const props = defineProps({
    selected: {
        type: Object,
        default: null,
    },
    visible: {
        type: Boolean,
        default: false,
    },
})

const route = useRoute()
const emit = defineEmits(['update:visible', 'save'])
const visible = computed({
    get: () => props.visible,
    set: (val) => emit('update:visible', val),
})

function close() {
    emit('update:visible', false)
    resetForm()
}

// Schedule data
const todaySchedules = ref([])
const selectedScheduleId = ref(null)
const subjects = ref([])

// Local form state
const form = reactive({
    id: null,
    teacher_id: null,
    subject_ids: [],
    class_id: null,
    date: '',
    theme: '',
    activity: '',
    notes: '',
    active: true,
})

// Load today's schedules
const loadTodaySchedules = async () => {
    try {
        const { ok, data, error } = await apiRequest('schedules/teacher/today')
        if (ok) {
            todaySchedules.value = data?.map((row) => {
                return {
                    ...row,
                    label: row.display_name,
                    value: row.id
                }
            }) || []
        }
    } catch (err) {
        console.error('Failed to load schedules:', err)
    }
}

// Handle schedule selection change
const onScheduleChange = (schedule) => {
    if (schedule) {
        form.teacher_id = schedule.teacher_id
        form.class_id = schedule.class_id
    }
}

// Handle schedules loaded from SelectServerSide
const onSchedulesLoaded = (data) => {
    todaySchedules.value = data || []
}

// Load schedules on mount
onMounted(() => {
    form.date = new Date().toISOString().split('T')[0]
    
    loadTodaySchedules()
})

function fillForm(src) {
    if (!src) return

    Object.assign(form, {
        ...src,
        active: src.active == 1 ? true : false,
        subject_ids: src.subjects ? src.subjects.map(s => s.subject_id) : []
    })
}

watch(
    () => props.visible,
    (val) => {
        if (val) {
            // Check for query params from my-schedules
            if (route.query.schedule_id && !props.selected) {
                // Auto-fill from query params
                form.teacher_id = route.query.teacher_id || null
                form.class_id = route.query.class_id || null
                selectedScheduleId.value = route.query.schedule_id || null
                // Set single subject_id to subject_ids array
                if (route.query.subject_id) {
                    form.subject_ids = [parseInt(route.query.subject_id)]
                }
            } else if (props.selected) {
                fillForm(props.selected)
            }
        } else {
            resetForm()
        }
    }
)

function resetForm() {
    Object.assign(form, {
        id: null,
        teacher_id: null,
        subject_ids: [],
        class_id: null,
        date: '',
        theme: '',
        activity: '',
        notes: '',
        active: true,
    })
}

function saveItem() {
    emit('save', { ...form })
}

defineExpose({
    close,
})
</script>
