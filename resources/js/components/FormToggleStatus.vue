<template>
    <div class="form-check form-switch">
        <input
            :id="id"
            class="form-check-input"
            type="checkbox"
            :checked="modelValue"
            v-bind="$attrs"
            @change="$emit('update:modelValue', $event.target.checked)"
        />
        <label class="form-check-label" :for="id">
            {{ props?.label }}
        </label>
    </div>
</template>

<script setup>
const props = defineProps({
    modelValue: {
        type: Boolean,
        required: true,
    },
    id: {
        type: String,
        default: () => `toggle-${Math.random().toString(36).substr(2, 9)}`,
    },
    label: {
        type: String,
        default: 'Aktif',
    },
})
</script>
