<template>
    <div class="camera-capture">
        <!-- Camera Preview -->
        <div v-if="!capturedImage" class="camera-container">
            <video
                ref="videoElement"
                autoplay
                playsinline
                muted
                class="camera-preview"
            ></video>
            
            <!-- Loading State -->
            <div v-if="loading" class="camera-loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Mengakses kamera...</p>
            </div>
            
            <!-- Error State -->
            <div v-if="error" class="camera-error">
                <i class="fas fa-exclamation-triangle"></i>
                <p>{{ error }}</p>
                <button @click="startCamera" class="btn btn-sm btn-primary">
                    <i class="fas fa-redo"></i> Coba Lagi
                </button>
            </div>
        </div>

        <!-- Captured Image Preview -->
        <div v-else class="captured-container">
            <img :src="capturedImage" alt="Captured" class="captured-image" />
            <div class="captured-actions">
                <button @click="retakePhoto" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Ulang
                </button>
                <button @click="confirmCapture" class="btn btn-primary">
                    <i class="fas fa-check"></i> Gunakan
                </button>
            </div>
        </div>

        <!-- Capture Button -->
        <div v-if="!loading && !error && !capturedImage" class="capture-controls">
            <button @click="capturePhoto" class="btn btn-lg btn-primary capture-btn">
                <i class="fas fa-camera"></i>
                <span>Ambil Foto</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, defineEmits } from 'vue'

const emit = defineEmits(['capture'])

// State
const videoElement = ref(null)
const loading = ref(false)
const error = ref('')
const capturedImage = ref('')
const stream = ref(null)

// Browser and Context Check
const checkBrowserSupport = () => {
    // Check if navigator.mediaDevices exists
    if (!navigator.mediaDevices) {
        return {
            supported: false,
            message: 'Browser tidak mendukung akses kamera. Silakan gunakan Chrome, Firefox, atau Edge versi terbaru.'
        }
    }

    // Check if getUserMedia exists
    if (!navigator.mediaDevices.getUserMedia) {
        return {
            supported: false,
            message: 'Browser tidak mendukung MediaDevices API. Silakan update browser ke versi terbaru.'
        }
    }

    // Check secure context for production
    // Allow localhost and common Valet domains for development
    const isDevelopmentDomain = 
        location.hostname === 'localhost' ||
        location.hostname === '127.0.0.1' ||
        location.hostname.endsWith('.test') ||
        location.hostname.includes('satupintusekolah.test')

    if (!isDevelopmentDomain && location.protocol !== 'https:') {
        return {
            supported: false,
            message: 'Akses kamera membutuhkan koneksi HTTPS. Untuk development lokal, gunakan localhost, HTTPS, atau Valet domain (.test).'
        }
    }

    return { supported: true }
}

// Camera Functions
const startCamera = async () => {
    loading.value = true
    error.value = ''
    
    // First check browser support
    const browserCheck = checkBrowserSupport()
    if (!browserCheck.supported) {
        error.value = browserCheck.message
        loading.value = false
        return
    }
    
    try {
        const constraints = {
            video: {
                facingMode: 'user', // Front camera
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        }

        stream.value = await navigator.mediaDevices.getUserMedia(constraints)
        
        if (videoElement.value) {
            videoElement.value.srcObject = stream.value
        }
    } catch (err) {
        console.error('Camera access error:', err)
        error.value = getErrorMessage(err)
    } finally {
        loading.value = false
    }
}

const capturePhoto = () => {
    if (!videoElement.value || !stream.value) return

    const canvas = document.createElement('canvas')
    const context = canvas.getContext('2d')
    
    // Set canvas size to match video
    canvas.width = videoElement.value.videoWidth
    canvas.height = videoElement.value.videoHeight
    
    // Draw video frame to canvas
    context.drawImage(videoElement.value, 0, 0)
    
    // Convert to blob then to data URL
    canvas.toBlob((blob) => {
        const reader = new FileReader()
        reader.onload = () => {
            capturedImage.value = reader.result
            stopCamera()
        }
        reader.readAsDataURL(blob)
    }, 'image/jpeg', 0.8) // 80% quality
}

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop())
        stream.value = null
    }
    if (videoElement.value) {
        videoElement.value.srcObject = null
    }
}

const retakePhoto = () => {
    capturedImage.value = ''
    startCamera()
}

const confirmCapture = () => {
    if (capturedImage.value) {
        emit('capture', capturedImage.value)
    }
}

const getErrorMessage = (error) => {
    switch (error.name) {
        case 'NotAllowedError':
            return 'Akses kamera ditolak. Silakan izinkan akses kamera di browser Anda.'
        case 'NotFoundError':
            return 'Tidak ada kamera yang ditemukan di perangkat ini.'
        case 'NotSupportedError':
            return 'Browser tidak mendukung akses kamera.'
        case 'NotReadableError':
            return 'Kamera sedang digunakan oleh aplikasi lain.'
        default:
            return 'Terjadi kesalahan saat mengakses kamera: ' + error.message
    }
}

// Lifecycle
onMounted(() => {
    startCamera()
})

onUnmounted(() => {
    stopCamera()
})
</script>

<style scoped>
.camera-capture {
    max-width: 500px;
    margin: 0 auto;
    text-align: center;
}

.camera-container {
    position: relative;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.camera-preview {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 12px;
}

.camera-loading, .camera-error {
    padding: 3rem 2rem;
    color: white;
    text-align: center;
    background: rgba(0, 0, 0, 0.7);
    border-radius: 12px;
}

.camera-error {
    color: #dc3545;
}

.camera-error i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.camera-error p {
    margin-bottom: 1rem;
}

.captured-container {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.captured-image {
    width: 100%;
    max-width: 400px;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.captured-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.capture-controls {
    margin-top: 1rem;
}

.capture-btn {
    min-height: 60px;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.capture-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.capture-btn i {
    margin-right: 0.5rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .camera-capture {
        max-width: 100%;
        padding: 0 1rem;
    }
    
    .capture-btn {
        width: 100%;
        min-height: 50px;
    }
    
    .captured-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .captured-actions button {
        width: 100%;
    }
}

/* Animation */
.spinner-border {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    vertical-align: text-bottom;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
