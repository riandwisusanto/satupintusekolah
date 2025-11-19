<template>
    <FormDrawer
        v-model="visible"
        :title="selected ? 'Ubah Pengguna' : 'Tambah Pengguna'"
        width="500"
        @save="saveItem"
    >
        <FormInput v-model="form.name" label="Nama" required />
        <FormInput type="email" v-model="form.email" label="Email" required />
        <FormInputPhone v-model="form.phone" label="No. HP" />
        <FormInputNIP v-model="form.nip" label="NIP" />
        <FormSelectRole v-model="form.role_id" label="Role" required />
        <FormInput
            v-model="form.password"
            :label="!form.id ? 'Password' : 'Password Baru'"
            type="password"
            :required="!form.id"
        />
        <small v-if="form.id"
            >* Biarkan kosong jika tidak ubah password</small
        >
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

console.log({props});


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
    phone: '',
    nip: '',
    email: '',
    active: true,
})

function fillForm(src) {
    if (!src) return
    // console.log(src);

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
        phone: '',
        nip: '',
        email: '',
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
