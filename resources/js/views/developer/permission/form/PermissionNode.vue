<template>
    <div class="mb-2">
        <!-- LEAF -->
        <label v-if="isLeaf" class="flex items-center gap-2">
            <input type="checkbox" :checked="isChecked" @change="toggle" />
            {{ node.label }}
        </label>

        <!-- PARENT -->
        <div v-else>
            <div class="flex items-center gap-2 font-semibold">
                <button
                    type="button"
                    class="text-gray-500 hover:text-gray-700 transition"
                    @click="expanded = !expanded"
                >
                    <span v-if="expanded">▼</span>
                    <span v-else>▶</span>
                </button>

                <input
                    type="checkbox"
                    :checked="allChildrenChecked"
                    :indeterminate="someChildrenChecked && !allChildrenChecked"
                    @change="toggle"
                />
                {{ node.label }}
            </div>
        </div>

        <!-- CHILDREN -->
        <transition name="fade">
            <div v-if="expanded && hasChildren" class="ms-6 mt-1 border-l border-gray-200 ps-2">
                <PermissionNode
                    v-for="child in node.children"
                    :key="child.value || child.label"
                    :node="child"
                    :model-value="modelValue"
                    @update:model-value="emit('update:modelValue', $event)"
                />
            </div>
        </transition>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    node: Object,
    modelValue: Array,
})

const emit = defineEmits(['update:modelValue'])

const isLeaf = computed(() => !!props.node.value)
const hasChildren = computed(
    () => Array.isArray(props.node.children) && props.node.children.length > 0
)

const expanded = ref(false)

const isChecked = computed(() => props.modelValue.includes(props.node.value))

function getAllChildValues(node) {
    if (!node.children || !node.children.length) {
        return node.value ? [node.value] : []
    }

    let values = []
    for (const child of node.children) {
        values = values.concat(getAllChildValues(child))
    }

    if (node.value) values.push(node.value)
    return values
}

const allChildrenValues = computed(() => getAllChildValues(props.node))

const allChildrenChecked = computed(() =>
    allChildrenValues.value.every((val) => props.modelValue.includes(val))
)

const someChildrenChecked = computed(() =>
    allChildrenValues.value.some((val) => props.modelValue.includes(val))
)

function toggle(event) {
    const newValues = [...props.modelValue]
    const valuesToUpdate = allChildrenValues.value

    if (event.target.checked) {
        valuesToUpdate.forEach((val) => {
            if (!newValues.includes(val)) newValues.push(val)
        })
    } else {
        valuesToUpdate.forEach((val) => {
            const idx = newValues.indexOf(val)
            if (idx !== -1) newValues.splice(idx, 1)
        })
    }

    emit('update:modelValue', newValues)
}
</script>

<style scoped>
label {
    cursor: pointer;
}

.fade-enter-active,
.fade-leave-active {
    transition: all 0.2s ease;
    overflow: hidden;
}
.fade-enter-from,
.fade-leave-to {
    max-height: 0;
    opacity: 0;
}
.fade-enter-to,
.fade-leave-from {
    max-height: 500px;
    opacity: 1;
}
</style>
