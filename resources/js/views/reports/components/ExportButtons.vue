<script setup>
import { ref } from 'vue'
import { apiRequest } from '@/lib/apiClient'
import { alertSuccess, alertError } from '@/lib/alert'

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({})
    },
    reportType: {
        type: String,
        required: true
    }
})

const loading = ref(false)

const exportPdf = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams(props.filters).toString()
        const url = `${import.meta.env.VITE_API_PATH}/api/v1/reports/${props.reportType}/export-pdf?${params}`
        
        // Create temporary link and trigger download
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `laporan-${props.reportType}-${Date.now()}.pdf`)
        link.setAttribute('target', '_blank')
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        
        alertSuccess('Export PDF berhasil!')
    } catch (err) {
        alertError('Gagal export PDF: ' + err.message)
    } finally {
        loading.value = false
    }
}

const exportExcel = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams(props.filters).toString()
        const url = `${import.meta.env.VITE_API_PATH}/api/v1/reports/${props.reportType}/export-excel?${params}`
        
        // Create temporary link and trigger download
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `laporan-${props.reportType}-${Date.now()}.xlsx`)
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        
        alertSuccess('Export Excel berhasil!')
    } catch (err) {
        alertError('Gagal export Excel: ' + err.message)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="export-buttons">
        <button 
            type="button" 
            class="btn btn-danger shadow-sm"
            :disabled="loading"
            @click="exportPdf"
        >
            <i class="fas fa-file-pdf mr-2"></i>
            {{ loading ? 'Exporting...' : 'Export PDF' }}
        </button>
        <button 
            type="button" 
            class="btn btn-success shadow-sm ml-2"
            :disabled="loading"
            @click="exportExcel"
        >
            <i class="fas fa-file-excel mr-2"></i>
            {{ loading ? 'Exporting...' : 'Export Excel' }}
        </button>
    </div>
</template>

<style scoped>
.export-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
