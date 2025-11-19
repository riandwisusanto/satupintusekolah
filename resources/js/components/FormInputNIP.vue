<template>
    <div class="form-group w-100">
        <label v-if="label" class="form-label">
            {{ label }}
            <span v-if="required" class="text-danger">*</span>
        </label>

        <input
            v-model="localValue"
            type="text"
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
    label: {
        type: String,
        default: 'NIP Guru'
    },
    placeholder: {
        type: String,
        default: 'Contoh: 198003122010011001',
    },
    maxLength: {
        type: Number,
        default: 18, // umum NIP panjangnya 18 digit
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

    if (props.required && !val) return 'NIP wajib diisi'
    if (val && !/^\d+$/.test(val)) return 'NIP hanya boleh berisi angka'
    if (val && val.length !== props.maxLength)
        return `NIP harus terdiri dari ${props.maxLength} digit`

    return ''
})

function onInput(e) {
    const digits = e.target.value.replace(/\D/g, '').slice(0, props.maxLength)
    localValue.value = digits
}
</script>
