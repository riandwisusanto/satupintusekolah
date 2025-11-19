<template>
    <div class="form-group w-100">
        <label v-if="label" class="form-label">
            {{ label }}
            <span v-if="required" class="text-danger">*</span>
        </label>
        <input
            v-model="localValue"
            type="tel"
            class="form-control"
            :placeholder="placeholder"
            :maxlength="maxLength"
            :required="required"
            :class="{ 'is-invalid': error }"
            @input="onInput"
        />
        <div v-if="error" class="invalid-feedback">{{ error }}</div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'

const props = defineProps({
    modelValue: String,
    label: String,
    placeholder: {
        type: String,
        default: 'Contoh: 081234567890',
    },
    maxLength: {
        type: Number,
        default: 13,
    },
    required: Boolean,
})

const emit = defineEmits(['update:modelValue'])

const localValue = ref(props.modelValue || '')

watch(
    () => props.modelValue,
    (val) => {
        if (val !== localValue.value) localValue.value = val
    }
)

watch(localValue, (val) => {
    emit('update:modelValue', val)
})

const error = computed(() => {
    const val = localValue.value
    if (props.required && !val) return 'Nomor HP wajib diisi'
    if (val && !/^\d+$/.test(val)) return 'Nomor HP hanya boleh berisi angka'
    if (val && val.length > props.maxLength) return `Nomor HP maksimal ${props.maxLength} digit`
    return ''
})

function onInput(e) {
    const digits = e.target.value.replace(/\D/g, '').slice(0, props.maxLength)
    localValue.value = digits
}
</script>
