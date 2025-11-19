<template>
    <div class="form-group">
        <label v-if="label" :for="id" class="form-label">
            {{ label }} <span v-if="required" class="text-danger">*</span>
        </label>
        <select
            :id="id"
            v-model="modelValue"
            class="form-control"
            :class="{ 'is-invalid': error }"
            :required="required"
            @change="handleChange"
        >
            <option value="" disabled>Pilih Kelas</option>
            <option
                v-for="item in options"
                :key="item.id"
                :value="item.id"
            >
                {{ item.name }}
            </option>
        </select>
        <div v-if="error" class="invalid-feedback">
            {{ error }}
        </div>
        <small v-if="help" class="form-text text-muted">{{ help }}</small>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiRequest } from '@/lib/apiClient'

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: '',
    },
    help: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).substr(2, 9)}`,
    },
})

const emit = defineEmits(['update:modelValue', 'change'])

const options = ref([])
const loading = ref(false)

const modelValue = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
})

const handleChange = (event) => {
    emit('change', event.target.value)
}

const fetchClassrooms = async () => {
    loading.value = true
    try {
        const { ok, data } = await apiRequest('classrooms', {
            method: 'get',
            params: {
                per_page: 100,
                active: 1,
            },
        })

        if (ok && data?.data) {
            options.value = data.data
        }
    } catch (error) {
        console.error('Error fetching classrooms:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchClassrooms()
})
</script>
