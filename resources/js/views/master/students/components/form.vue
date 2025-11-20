<template>
    <FormDrawer
        v-model="visible"
        :title="selected ? 'Ubah Siswa' : 'Tambah Siswa'"
        width="500"
        @save="saveItem"
    >
        <FormInput v-model="form.nis" label="NIS" required />
        <FormInput v-model="form.name" label="Nama" required />
        <div class="form-group">
            <label>Jenis Kelamin <span class="text-danger">*</span></label>
            <select v-model="form.gender" class="form-control" required>
                <option value="">Pilih Jenis Kelamin</option>
                <option value="laki-laki">Laki-laki</option>
                <option value="perempuan">Perempuan</option>
            </select>
        </div>
        <FormSelectClassroom v-model="form.class_id" label="Kelas" required />
        <FormToggleStatus v-model="form.active" />
    </FormDrawer>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import FormDrawer from '../../../../components/FormDrawer.vue'
import FormSelectClassroom from '@/components/FormSelectClassroom.vue'

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
    nis: '',
    name: '',
    gender: '',
    class_id: '',
    active: true,
})

function fillForm(src) {
    if (!src) return

    Object.assign(form, {
        ...src,
        active: src.active == 1 ? true : false,
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
        nis: '',
        name: '',
        gender: '',
        class_id: '',
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
