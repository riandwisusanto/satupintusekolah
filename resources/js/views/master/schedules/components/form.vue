<template>
    <FormDrawer
        v-model="visible"
        :title="selected ? 'Ubah Jadwal' : 'Tambah Jadwal'"
        width="600"
        @save="saveItem"
    >
        <FormSelectTeacher v-model="form.teacher_id" label="Guru" required />
        <FormSelectSubject v-model="form.subject_id" label="Mata Pelajaran" required />
        <FormSelectClassroom v-model="form.class_id" label="Kelas" required />
        
        <SelectServerSide
            v-model="form.day"
            label="Hari"
            placeholder="Pilih Hari"
            :options="[
                {label: 'Senin', value: 'Senin'},
                {label: 'Selasa', value: 'Selasa'},
                {label: 'Rabu', value: 'Rabu'},
                {label: 'Kamis', value: 'Kamis'},
                {label: 'Jumat', value: 'Jumat'},
                {label: 'Sabtu', value: 'Sabtu'},
            ]"
            :serverside="false"
            required
        />

        <FormInput 
            v-model="form.start_time" 
            label="Waktu Mulai" 
            type="time" 
            required 
        />
        
        <FormInput 
            v-model="form.end_time" 
            label="Waktu Selesai" 
            type="time" 
            required 
        />
    </FormDrawer>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'

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

const emit = defineEmits(['update:visible', 'save'])
const visible = computed({
    get: () => props.visible,
    set: (val) => emit('update:visible', val),
})

function close() {
    emit('update:visible', false)
    resetForm()
}

// Local form state
const form = reactive({
    id: null,
    teacher_id: null,
    subject_id: null,
    class_id: null,
    day: '',
    start_time: '',
    end_time: '',
})

function fillForm(src) {
    if (!src) return

    Object.assign(form, {
        ...src,
    })
}

watch(
    () => props.visible,
    (val) => {
        if (val && props.selected) {
            fillForm(props.selected)
        } else if (!val) {
            resetForm()
        }
    }
)

function resetForm() {
    Object.assign(form, {
        id: null,
        teacher_id: null,
        subject_id: null,
        class_id: null,
        day: '',
        start_time: '',
        end_time: '',
    })
}

function saveItem() {
    emit('save', { ...form })
}

defineExpose({
    close,
})
</script>
