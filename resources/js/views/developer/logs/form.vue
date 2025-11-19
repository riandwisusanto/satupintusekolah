<template>
    <!-- Overlay -->
    <transition name="fade">
        <div v-if="modelValue" class="drawer-overlay" @click.self="close" />
    </transition>

    <!-- Drawer -->
    <transition name="slide">
        <aside v-if="modelValue" class="drawer-panel px-4 py-3">
            <!-- Header -->
            <div class="d-flex justify-between items-center border-bottom pb-2 mb-3">
                <h5 class="mb-0">🧾 Log Detail</h5>
                <button class="btn btn-sm btn-outline-secondary" @click="close">
                    <i class="fas fa-times" />
                </button>
            </div>

            <!-- Card Content -->
            <div v-if="props?.type === 'request'" class="row">
                <div class="col-md-6">
                    <LogCard title="Method">&nbsp;{{ log?.method }}</LogCard>
                </div>
                <div class="col-md-6">
                    <LogCard title="URL">&nbsp;{{ log?.url.split('v1')[1] }}</LogCard>
                </div>
                <div class="col-md-6">
                    <LogCard title="IP Address">&nbsp;{{ log?.ip }}</LogCard>
                </div>
                <div class="col-md-6">
                    <LogCard title="User">&nbsp;{{ log?.user?.name }}</LogCard>
                </div>
                <div class="col-md-6">
                    <LogCard title="Response Code">&nbsp;{{ log?.response_code }}</LogCard>
                </div>
                <div class="col-md-6">
                    <LogCard title="Created At"
                        >{{
                            new Date(log?.created_at).toLocaleString('id-ID', {
                                dateStyle: 'medium',
                                timeStyle: 'short',
                            })
                        }}
                    </LogCard>
                </div>
                <div class="col-md-12">
                    <LogCard title="Payload" :copyable="formattedPayload">
                        <pre class="json-block">{{ formattedPayload }}</pre>
                    </LogCard>
                </div>
                <div class="col-md-12">
                    <LogCard title="Response Body" :copyable="formattedResponse">
                        <pre class="json-block">{{ formattedResponse }}</pre>
                    </LogCard>
                </div>
            </div>

            <div v-else class="row">
                <div class="col-md-6">
                    <LogCard title="User">&nbsp;{{ log?.user?.name }}</LogCard>
                </div>
                <div class="col-md-6">
                    <LogCard title="Event">&nbsp;{{ log?.event }}</LogCard>
                </div>
                <div class="col-md-6">
                    <LogCard title="Created At"
                        >{{
                            new Date(log?.created_at).toLocaleString('id-ID', {
                                dateStyle: 'medium',
                                timeStyle: 'short',
                            })
                        }}
                    </LogCard>
                </div>
                <div class="col-md-6">
                    <LogCard title="Model">&nbsp;{{ log?.model_type }}</LogCard>
                </div>
                <div class="col-md-12">
                    <LogCard title="Model Raw" :copyable="modelResponse">
                        <pre class="json-block">{{ modelResponse }}</pre>
                    </LogCard>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-end mt-4 border-top pt-3">
                <button class="btn btn-secondary" @click="close">Tutup</button>
            </div>
        </aside>
    </transition>
</template>

<script setup>
import { computed } from 'vue'
import { useClipboard } from '@vueuse/core'
import { alertSuccess } from '../../../lib/alert'

const props = defineProps({
    modelValue: Boolean,
    log: Object,
    type: { type: String, default: 'request' },
})
const emit = defineEmits(['update:modelValue'])

const close = () => emit('update:modelValue', false)

const formattedPayload = computed(() =>
    typeof props.log?.payload === 'object'
        ? JSON.stringify(props.log.payload, null, 2)
        : props.log?.payload || '-'
)

const formattedResponse = computed(() =>
    typeof props.log?.response_body === 'object'
        ? JSON.stringify(props.log.response_body, null, 2)
        : props.log?.response_body || '-'
)

const modelResponse = computed(() =>
    typeof props.log?.model === 'object'
        ? JSON.stringify(props.log.model, null, 2)
        : props.log?.model || '-'
)

const { copy } = useClipboard()
</script>

<!-- Card Slot Component -->
<script>
export default {
    components: {
        LogCard: {
            props: ['title', 'copyable'],
            emits: ['copy'],
            setup(props) {
                const { copy } = useClipboard()
                const handleCopy = () => {
                    if (props.copyable) {
                        copy(props.copyable)
                        alertSuccess('Berhasil disalin')
                    }
                }
                return { handleCopy }
            },
            template: `
        <div class="card border rounded shadow-sm position-relative">
          <div class="card-body p-3">
            <h6 class="card-title text-muted small d-flex justify-between align-items-center mb-2">
              <span>{{ title }} : </span>
              <button
                v-if="copyable"
                class="btn btn-sm btn-light border"
                @click="handleCopy"
              >
                <i class="fas fa-copy me-1" /> Salin
              </button>
            </h6>
            <slot />
          </div>
        </div>
      `,
        },
    },
}
</script>

<style scoped>
.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1040;
}

.drawer-panel {
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
    width: 100%;
    max-width: 650px;
    background: #fff;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

/* Transitions */
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

.json-block {
    background: #f8f9fa;
    border-radius: 4px;
    padding: 10px;
    font-size: 0.85rem;
    max-height: 300px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-word;
    font-family: monospace;
}
</style>
