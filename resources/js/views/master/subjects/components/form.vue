<template>
    <FormDrawer
        v-model="visible"
        :title="selected ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran'"
        width="500"
        @save="saveItem"
    >
        <FormInput v-model="form.name" label="Nama Mata Pelajaran" required />
        <FormToggleStatus v-model="form.active" />
    </FormDrawer>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import FormDrawer from '../../../../components/FormDrawer.vue'

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
