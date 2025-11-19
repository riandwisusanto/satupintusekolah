<template>
  <div
    v-show="shouldRender"
    class="tab-pane fade"
    :class="{ show: isActive, active: isActive }"
  >
    <slot />
  </div>
</template>

<script setup>
import { inject, onMounted, computed, ref, watch } from 'vue'

const props = defineProps({
  name: { type: String, required: true },
  label: { type: String, required: true },
  icon: { type: String, default: '' }
})

const activeTab = inject('activeTab')
const registerTab = inject('registerTab')

const isActive = computed(() => activeTab.value === props.name)
const hasRendered = ref(false)

const shouldRender = computed(() => isActive.value || hasRendered.value)

watch(isActive, val => {
  if (val) hasRendered.value = true
})

onMounted(() => {
  registerTab({
    name: props.name,
    label: props.label,
    icon: props.icon
  })
})
</script>
