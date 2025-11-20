<template>
    <SelectServerSide
        v-model="model"
        :name="name"
        :label="label"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :options="rankOptions"
        :serverside="false"
    />
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import SelectServerSide from './SelectServerSide.vue'
import { apiRequest } from '../lib/apiClient'

// Props
const props = defineProps({
    modelValue: [String, Number],
    name: {
        type: String,
        default: 'teacher_id',
    },
    label: {
        type: String,
        default: 'Guru',
    },
    placeholder: {
        type: String,
        default: 'Pilih Guru',
    },
    disabled: Boolean,
    required: Boolean,
})

const emit = defineEmits(['update:modelValue'])

// Reactive model bridging
const model = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
})

// Customer options
const rankOptions = ref([])

onMounted(async () => {
    fetchData()
})

const fetchData = async () => {
    const { ok, data, error } = await apiRequest('users/teachers')
    if (ok) {
        rankOptions.value = data?.data?.teachers?.map((b) => ({
            label: b?.name,
            value: b?.id,
        }))
    } else {
        console.error('[ProvinceSelect] gagal mengambil data kota', error)
    }
}
</script>
