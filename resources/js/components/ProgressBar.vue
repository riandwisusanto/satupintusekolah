<!-- components/ProgressBar.vue -->
<template>
    <div
        v-show="visible"
        class="progress-bar"
        :style="{ width: progress + '%', transitionDuration: speed + 'ms' }"
    ></div>
</template>

<script setup>
import { ref } from 'vue'

const progress = ref(0)
const visible = ref(false)
const speed = 300

function start() {
    progress.value = 0
    visible.value = true
    const interval = setInterval(() => {
        if (progress.value < 90) {
            progress.value += 10
        } else {
            clearInterval(interval)
        }
    }, 200)
}

function finish() {
    progress.value = 100
    setTimeout(() => {
        visible.value = false
        progress.value = 0
    }, speed)
}

defineExpose({ start, finish })
</script>

<style scoped>
.progress-bar {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    background: rgb(34, 221, 109);
    z-index: 9999;
}
</style>
