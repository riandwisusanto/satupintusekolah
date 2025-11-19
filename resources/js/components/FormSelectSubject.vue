<template>
    <SelectServerSide
        v-model="selectedValue"
        :label="label"
        :placeholder="placeholder || '-- Pilih Mata Pelajaran --'"
        :required="required"
        :disabled="disabled"
        :name="name"
        :endpoint="endpoint"
        :serverside="true"
        label-key="name"
        value-key="id"
        @select="handleSelect"
    />
</template>

<script setup>
import { ref, watch } from 'vue'
import SelectServerSide from './SelectServerSide.vue'

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: 'Pilih Mata Pelajaran',
    },
    placeholder: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    name: {
        type: String,
        default: 'subject_id',
    },
})

const emit = defineEmits(['update:modelValue', 'select'])

const selectedValue = ref(props.modelValue)
const endpoint = 'subjects/options'

watch(() => props.modelValue, (newValue) => {
    selectedValue.value = newValue
})

watch(selectedValue, (newValue) => {
    emit('update:modelValue', newValue)
})

const handleSelect = (selectedOption) => {
    emit('select', selectedOption)
}
</script>
