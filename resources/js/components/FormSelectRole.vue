<template>
    <SelectServerSide
        v-model="model"
        :name="name"
        :label="label"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :options="roleOptions"
        :serverside="false"
    />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiRequest } from '@/lib/apiClient'

// Props
const props = defineProps({
    modelValue: [String, Number],
    name: {
        type: String,
        default: 'role_id',
    },
    label: {
        type: String,
        default: 'Role',
    },
    placeholder: {
        type: String,
        default: 'Pilih Role',
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
const roleOptions = ref([])

onMounted(async () => {
    const { ok, data, error } = await apiRequest('roles?all=true')
    if (ok) {
        roleOptions.value = data?.data?.map((b) => ({
            label: b?.label,
            value: b?.id,
        }))
    } else {
        console.error('[RankSelect] gagal mengambil data customer', error)
    }
})
</script>
