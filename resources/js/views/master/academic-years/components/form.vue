<template>
    <FormDrawer
        v-model="visible"
        :title="selected ? 'Ubah Tahun Ajaran' : 'Tambah Tahun Ajaran'"
        width="500"
        @save="saveItem"
    >
        <FormInput v-model="form.name" label="Nama Tahun Ajaran" required />
        <SelectServerSide
            label="Semester"
            v-model="form.semester"
            :options="[
                { label: 'Ganjil', value: 1 },
                { label: 'Genap', value: 2 },
            ]"
            :serverside="false"
            required
            />
        <FormInput v-model="form.start_date" label="Tanggal Mulai" type="date" required />
        <FormInput v-model="form.end_date" label="Tanggal Selesai" type="date" required />
        <FormToggleStatus v-model="form.active" />
    </FormDrawer>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import SelectServerSide from '../../../../components/SelectServerSide.vue'

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
    name: '',
    start_date: '',
    end_date: '',
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
        name: '',
        start_date: '',
        end_date: '',
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
