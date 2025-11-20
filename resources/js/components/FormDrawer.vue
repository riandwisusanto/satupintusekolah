<template>
    <teleport to="body">
        <!-- Overlay -->
        <transition name="fade">
            <div 
                v-if="visible" 
                class="drawer-overlay" 
                @click.self="close"
                @touchstart="handleTouchStart"
                @touchend="handleTouchEnd"
            />
        </transition>

        <!-- Drawer Panel -->
        <transition name="slide">
            <aside
                v-if="visible"
                ref="drawerPanel"
                class="drawer-panel card shadow-lg overflow-hidden"
                :class="{ 'drawer-panel--mobile': isMobile, 'drawer-panel--fullscreen': fullscreen }"
                :style="panelStyles"
                @touchstart="handleTouchStart"
                @touchmove="handleTouchMove"
                @touchend="handleTouchEnd"
            >
                <!-- Mobile Drag Handle -->
                <div v-if="isMobile" class="drag-handle">
                    <div class="drag-bar"></div>
                </div>

                <!-- Header -->
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <button 
                        type="button" 
                        class="btn btn-tool btn-sm" 
                        @click="close"
                        aria-label="Tutup form"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                    <h5 class="mb-0 flex-grow-1 text-center">{{ title }}</h5>
                    <div class="placeholder-btn"></div>
                </div>

                <!-- Body -->
                <div class="card-body flex-grow-1 overflow-y-auto">
                    <slot></slot>
                </div>

                <!-- Footer -->
                <div class="card-footer d-flex justify-content-end py-3">
                    <button 
                        type="button" 
                        class="btn btn-secondary mr-2" 
                        @click="close"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="btn btn-primary" 
                        @click="submit"
                    >
                        Simpan
                    </button>
                </div>
            </aside>
        </transition>
    </teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

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
    },
    fullscreen: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue', 'save'])

const drawerPanel = ref(null)
const touchStartX = ref(0)
const touchStartY = ref(0)
const isDragging = ref(false)

// Responsive detection
const isMobile = computed(() => {
    if (typeof window === 'undefined') return false
    return window.innerWidth <= 768
})

const panelStyles = computed(() => {
    // Auto fullscreen on mobile (inject logic)
    if (isMobile.value) {
        return {
            width: '100vw',
            height: '100dvh',
            maxWidth: 'none',
            right: '0'
        }
    }
    
    // Explicit fullscreen prop
    if (props.fullscreen) {
        return {
            width: '100vw',
            height: '100dvh',
            maxWidth: 'none',
            right: '0'
        }
    }
    
    // Responsive for tablet/desktop
    const isTablet = window.innerWidth > 768 && window.innerWidth <= 1024
    const desktopWidth = Math.min(props.width, window.innerWidth * 0.9)
    
    return {
        width: isTablet ? '80vw' : `${desktopWidth}px`,
        height: '100vh',
        maxWidth: isTablet ? '80vw' : `${desktopWidth}px`,
        right: '0'
    }
})

const visible = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
})

// Touch handlers for swipe to close
function handleTouchStart(e) {
    touchStartX.value = e.touches[0].clientX
    touchStartY.value = e.touches[0].clientY
    isDragging.value = true
}

function handleTouchMove(e) {
    if (!isDragging.value || !drawerPanel.value) return
    
    const touchX = e.touches[0].clientX
    const touchY = e.touches[0].clientY
    const deltaX = touchX - touchStartX.value
    const deltaY = touchY - touchStartY.value
    
    // Only allow horizontal swipe from left edge
    if (deltaX < -50 && Math.abs(deltaY) < 50) {
        drawerPanel.value.style.transform = `translateX(${deltaX}px)`
    }
}

function handleTouchEnd(e) {
    if (!isDragging.value || !drawerPanel.value) return
    
    const touchX = e.changedTouches[0].clientX
    const deltaX = touchX - touchStartX.value
    
    // Close if swiped left more than 100px
    if (deltaX < -100) {
        close()
    } else {
        // Reset position
        drawerPanel.value.style.transform = ''
    }
    
    isDragging.value = false
}

// Keyboard handler
function handleKeydown(e) {
    if (e.key === 'Escape' && visible.value) {
        close()
    }
}

// Window resize handler
function handleResize() {
    // Force re-render panel styles
    nextTick(() => {
        if (drawerPanel.value) {
            Object.assign(drawerPanel.value.style, panelStyles.value)
        }
    })
}

function close() {
    emit('update:modelValue', false)
}

function submit() {
    emit('save')
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
    window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
    window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 2000;
    -webkit-backdrop-filter: blur(4px);
}

.drawer-panel {
    position: fixed;
    top: 0;
    right: 0;
    background: #fff;
    z-index: 2001;
    display: flex;
    flex-direction: column;
    border-radius: 16px 0 0 16px;
    box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
}

.drawer-panel--mobile {
    border-radius: 0;
    width: 100vw !important;
    height: 100dvh !important;
    max-width: none !important;
    padding-top: env(safe-area-inset-top);
    padding-bottom: env(safe-area-inset-bottom);
}

.drawer-panel--fullscreen {
    border-radius: 0;
    width: 100vw !important;
    height: 100dvh !important;
    max-width: none !important;
}

.drag-handle {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    z-index: 10;
}

.drag-bar {
    width: 32px;
    height: 4px;
    background: #dee2e6;
    border-radius: 2px;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
    background: #fff;
    position: relative;
    z-index: 5;
}

.card-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    /* Space for sticky footer */
    padding-bottom: 80px;
}

.card-footer {
    border-top: 1px solid #e9ecef;
    background: #fff;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 5;
}

.placeholder-btn {
    width: 32px;
    height: 32px;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-enter-from {
    transform: translateX(100%);
}

.slide-leave-to {
    transform: translateX(100%);
}

/* Button hover states */
.btn-tool {
    transition: all 0.2s ease;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-tool:hover {
    background: rgba(0, 0, 0, 0.05);
    transform: scale(1.05);
}

.btn-tool:active {
    transform: scale(0.95);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
        padding-bottom: 80px;
    }
    
    .card-header,
    .card-footer {
        padding: 0.75rem 1rem;
    }
    
    .btn {
        min-height: 44px; /* Touch-friendly size */
    }
}

/* Focus styles for accessibility */
.btn:focus-visible {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* Smooth scrolling */
.card-body {
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

/* Prevent bounce on mobile */
.drawer-panel--mobile {
    overscroll-behavior: contain;
}
</style>
