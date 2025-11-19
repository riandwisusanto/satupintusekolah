<template>
    <div :class="wrapperClass">
        <!-- Label -->
        <label v-if="label" :for="uid" class="form-label">
            {{ label }}<span v-if="required" class="text-danger">*</span>
        </label>

        <!-- Input field -->
        <input
            v-if="type !== 'textarea'"
            :id="uid"
            :class="money ? 'form-control text-end' : 'form-control'"
            :name="name"
            :type="money ? 'text' : type"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :value="displayValue"
            v-bind="inputProps"
            @input="onInput"
        />

        <!-- Textarea -->
        <textarea
            v-else
            :id="uid"
            v-model="displayValue"
            class="form-control"
            :name="name"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            v-bind="inputProps"
            @input="onInput"
        />

        <!-- Help & Error -->
        <small v-if="help" class="form-text text-muted">{{ help }}</small>
        <small v-if="error" class="form-text text-danger">{{ error }}</small>
    </div>
</template>

<script setup>
import { nextTick } from 'vue'
import { computed } from 'vue'

const props = defineProps({
    modelValue: [String, Number, Date],
    type: {
        type: String,
        default: 'text',
        validator: (v) => ['text', 'number', 'date', 'textarea', 'email'].includes(v),
    },
    money: Boolean,
    label: String,
    name: String,
    id: String,
    required: Boolean,
    placeholder: String,
    disabled: Boolean,
    help: String,
    error: String,
    wrapperClass: { type: String, default: 'form-group' },
    inputProps: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue'])

const uid = computed(() => props.id || props.name || `fi-${Math.random().toString(36).slice(2)}`)

function formatCurrency(val) {
    if (val === '' || val == null || isNaN(val)) return ''
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(val)
}

function parseCurrency(str) {
    const num = str.replace(/[^\d]/g, '')
    return Number(num || 0)
}

function onInput(event) {
    const val = event.target.value

    if (props.money) {
        let numeric = parseCurrency(val)

        const min = typeof props.inputProps?.min === 'number' ? props.inputProps.min : undefined
        const max = typeof props.inputProps?.max === 'number' ? props.inputProps.max : undefined

        if (max !== undefined && numeric > max) numeric = max
        if (min !== undefined && numeric < min) numeric = min

        emit('update:modelValue', numeric)

        nextTick(() => {
            event.target.value = formatCurrency(numeric)
        })
    } else {
        let newVal = val

        if (props.type === 'number') {
            const num = parseFloat(val)
            const min = props.inputProps?.min
            const max = props.inputProps?.max

            if (!isNaN(num)) {
                if (typeof max === 'number' && num > max) newVal = max
                if (typeof min === 'number' && num < min) newVal = min
            }
        }

        emit('update:modelValue', newVal)
    }
}

function onlyAllowDigits(e) {
    const char = String.fromCharCode(e.which)
    if (!/[0-9]/.test(char)) e.preventDefault()
}

const displayValue = computed(() => {
    if (props.money) return formatCurrency(props.modelValue)
    if (props.type === 'date') {
        if (props.modelValue instanceof Date) return props.modelValue.toISOString().split('T')[0]
        return props.modelValue
    }
    return props.modelValue
})
</script>

<style scoped>
.form-label {
    font-weight: 600;
}
.text-end {
    text-align: right;
}
</style>
