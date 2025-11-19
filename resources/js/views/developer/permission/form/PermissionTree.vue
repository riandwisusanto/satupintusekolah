<template>
    <div>
        <PermissionNode
            v-for="node in permissions"
            :key="node.label"
            :node="node"
            :model-value="modelValue"
            @update:model-value="emit('update:modelValue', $event)"
        />
    </div>
</template>

<script setup>
import PermissionNode from './PermissionNode.vue'

defineProps({
    permissions: Array,
    modelValue: Array,
})

const emit = defineEmits(['update:modelValue'])
</script>
