<template>
    <div :class="wrapperClass">
        <!-- ===== Label ===== -->
        <label v-if="label" :for="uid" class="form-label">
            {{ label }}<span v-if="required" class="text-danger">*</span>
        </label>

        <!-- ===== Select Component ===== -->
        <VueSelect
            :id="uid"
            v-model="selected"
            :name="name"
            :options="serverside ? remoteOptions : options"
            :placeholder="placeholder"
            :is-disabled="disabled"
            :is-loading="loading"
            :reduce="(opt) => opt.value"
            :teleport="teleport"
            label="label"
            @open="ensureLoaded"
            @search="handleSearch"
        >
            <template #no-options>
                <div v-if="loading">Loading...</div>
                <div v-else>No options found.</div>
            </template>
        </VueSelect>

        <input
            v-if="required"
            type="text"
            :name="name"
            :value="selected"
            required
            style="position: absolute; opacity: 0; height: 0; pointer-events: none"
            @invalid="handleInvalid"
        />

        <!-- Error / Help -->
        <small v-if="displayError" class="form-text text-danger">{{ displayError }}</small>
        <small v-if="help" class="form-text text-muted">{{ help }}</small>
        <small v-if="error" class="form-text text-danger">{{ error }}</small>
    </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import VueSelect from 'vue3-select-component'
import { apiRequest } from '../lib/apiClient'

// ===== Props =====
const props = defineProps({
    modelValue: [String, Number],
    label: String,
    name: String,
    id: String,
    required: Boolean,
    disabled: Boolean,
    placeholder: String,
    options: {
        type: Array,
        default: () => [],
    },
    serverside: Boolean,
    endpoint: String,
    fetchOptions: Function,
    labelKey: {
        type: String,
        default: 'name',
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    wrapperClass: {
        type: String,
        default: 'form-group',
    },
    help: String,
    error: String,
    excludeId: [String, Number],
    labelFormatter: {
        type: Function,
        default: null,
    },
    teleport: {
        type: String,
        default: undefined,
    },
})

const emit = defineEmits(['update:modelValue', 'load', 'select'])

// ===== Mounted guard =====
let isMounted = true
onUnmounted(() => {
    isMounted = false
})

// ===== Error handling =====
const internalError = ref(null)
function handleInvalid(e) {
    e.preventDefault()
    internalError.value = 'Field wajib diisi.'
}
const displayError = computed(() => internalError.value)

// ===== v-model bridge =====
const selected = ref(null)
watch(selected, (val) => {
    emit('update:modelValue', val)
    const list = props.serverside ? remoteOptions.value : props.options
    const selectedOption = list.find((opt) => opt.value === val)
    emit('select', selectedOption || null)
})
watch(
    () => props.modelValue,
    (val) => {
        selected.value = val
    },
    { immediate: true }
)
watch(selected, () => {
    internalError.value = null
})

// ===== ID =====
const uid = computed(
    () => props.id || props.name || `select-${Math.random().toString(36).slice(2)}`
)

// ===== Remote state =====
const loading = ref(false)
const remoteOptions = ref([])
const searchQuery = ref('')
const lastSearchQuery = ref('')
const initialOptions = ref([])

// ===== Fetch handler =====
const fetchFn = computed(() => {
    if (typeof props.fetchOptions === 'function') return props.fetchOptions
    if (props.endpoint) {
        return async (search = '') => {
            const hasQuery = props.endpoint.includes('?')
            const url = `${props.endpoint}${hasQuery ? '&' : '?'}search=${encodeURIComponent(search)}`
            const { ok, data } = await apiRequest(url)
            if (!ok) return []
            const items = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : []
            return items.map((item) => ({
                ...item,
                label: item[props.labelKey] ?? item.name ?? item.id,
                value: item[props.valueKey] ?? item.id,
            }))
        }
    }
    return null
})

// ===== Load options once opened =====
async function ensureLoaded(search = '') {
    if (!props.serverside) return
    if (!fetchFn.value || typeof fetchFn.value !== 'function') {
        console.warn('[SelectServerSide] fetchOptions or endpoint is required for serverside=true')
        return
    }

    // 🧠 kalau baru search, jangan reset ke search kosong
    const effectiveSearch = search || lastSearchQuery.value || ''
    loading.value = true
    try {
        const data = await fetchFn.value(effectiveSearch)
        if (!isMounted) return

        const fetchedOptions = (Array.isArray(data) ? data : [])
            .map((opt) => ({
                ...opt,
                label: props.labelFormatter
                    ? props.labelFormatter(opt)
                    : (opt[props.labelKey] ?? opt.label ?? opt.id),
                value: opt[props.valueKey] ?? opt.value ?? opt.id,
            }))
            .filter((opt) => opt.value !== props.excludeId)

        // Merge with initial options, avoiding duplicates
        const allOptions = [...initialOptions.value]
        fetchedOptions.forEach((fetchedOpt) => {
            if (!allOptions.some((existing) => existing.value === fetchedOpt.value)) {
                allOptions.push(fetchedOpt)
            }
        })

        remoteOptions.value = allOptions
        emit('load', remoteOptions.value)
    } finally {
        if (isMounted) loading.value = false
    }
}

// ===== Ensure initial option is included =====
function ensureInitialOption(option) {
    if (!option) return
    const existingIndex = initialOptions.value.findIndex((opt) => opt.value === option.value)
    if (existingIndex === -1) {
        initialOptions.value.push(option)
    } else {
        initialOptions.value[existingIndex] = option
    }

    // Also update remoteOptions if they're already loaded
    const existingRemoteIndex = remoteOptions.value.findIndex((opt) => opt.value === option.value)
    if (existingRemoteIndex === -1) {
        remoteOptions.value = [...remoteOptions.value, option]
    } else {
        remoteOptions.value[existingRemoteIndex] = option
    }
}

// ===== Search handler (debounced) =====
let timeout = null
const handleSearch = (query) => {
    if (query === '' && searchQuery.value !== '') {
        return
    }
    searchQuery.value = query
    lastSearchQuery.value = query // ✅ simpan query terakhir

    if (!props.serverside) return
    if (!fetchFn.value || typeof fetchFn.value !== 'function') return
    if (timeout) clearTimeout(timeout)

    timeout = setTimeout(async () => {
        loading.value = true
        try {
            const data = await fetchFn.value(query)
            if (!isMounted) return

            const fetchedOptions = (Array.isArray(data) ? data : [])
                .map((opt) => ({
                    ...opt,
                    label: props.labelFormatter
                        ? props.labelFormatter(opt)
                        : (opt[props.labelKey] ?? opt.label ?? opt.id),
                    value: opt[props.valueKey] ?? opt.value ?? opt.id,
                }))
                .filter((opt) => opt.value !== props.excludeId)

            // Merge with initial options, avoiding duplicates
            const allOptions = [...initialOptions.value]
            fetchedOptions.forEach((fetchedOpt) => {
                if (!allOptions.some((existing) => existing.value === fetchedOpt.value)) {
                    allOptions.push(fetchedOpt)
                }
            })

            remoteOptions.value = allOptions
        } finally {
            if (isMounted) loading.value = false
        }
    }, 300)
}

// ===== Re-load when excludeId changes =====
watch(
    () => props.excludeId,
    () => {
        ensureLoaded()
    },
    { immediate: true }
)

defineExpose({ ensureLoaded, ensureInitialOption })
</script>

<style scoped>
.form-label {
    font-weight: 600;
}
</style>
