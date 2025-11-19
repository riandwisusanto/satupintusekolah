<template>
    <teleport to="body">
        <!-- Overlay -->
        <transition name="fade">
            <div v-if="visible" class="drawer-overlay" @click.self="close" />
        </transition>

        <!-- Drawer Panel -->
        <transition name="slide">
            <aside
                v-if="visible"
                class="drawer-panel card shadow-lg overflow-auto px-4 py-3"
                :style="{ maxWidth: width + 'px' }"
            >
                <!-- Header -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-tool" @click="close">
                        <i class="fas fa-times"></i>
                    </button>
                    <h5 class="mb-0">{{ title }}</h5>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <slot></slot>
                </div>

                <!-- Footer -->
                <div class="card-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary mr-2" @click="close">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" @click="submit">
                        Simpan
                    </button>
                </div>
            </aside>
        </transition>
    </teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    width: {
        type: Number,
        default: 600
    }
})

const emit = defineEmits(['update:modelValue', 'save'])

const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
})

function close() {
    emit('update:modelValue', false)
}

function submit() {
    emit('save')
}
</script>

<style scoped>
.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 2000;
}

.drawer-panel {
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
    width: 100%;
    background: #fff;
    z-index: 2001;
    display: flex;
    flex-direction: column;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}
.slide-enter-from {
    transform: translateX(100%);
}
.slide-leave-to {
    transform: translateX(100%);
}

.card-body {
    padding-bottom: 80px; /* jaga footer tetap visible */
}
</style>
