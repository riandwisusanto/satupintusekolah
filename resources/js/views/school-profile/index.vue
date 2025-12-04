<script setup>
import { ref, onMounted, computed } from 'vue'
import { apiRequest } from '../../lib/apiClient'
import { alertSuccess, alertError } from '../../lib/alert'
import { useUser } from '../../store'

// Form data
const formData = ref({
    school_name: '',
    school_address: '',
    school_phone: '',
    school_email: '',
})

const token = useUser().user?.accesstoken || null

// Logo upload
const logoFile = ref(null)
const logoPreview = ref('')
const isUploading = ref(false)

// Background image upload
const backgroundFile = ref(null)
const backgroundPreview = ref('')
const isUploadingBackground = ref(false)

// Form state
const loading = ref(false)
const saving = ref(false)

// Load current profile data
const loadProfile = async () => {
    try {
        loading.value = true
        
        // Get all school configurations
        const configs = [
            'school_name',
            'school_address', 
            'school_phone',
            'school_email',
            'school_logo',
        ]
        
        for (const config of configs) {
            const response = await apiRequest(`configurations/${config}`)
            if (response.ok && response.data) {
                if (config === 'school_logo') {
                    logoPreview.value = response.data ? `/storage/${response.data?.data}` : ''
                } else if (config === 'background_image') {
                    backgroundPreview.value = response.data ? `/storage/${response.data}` : ''
                } else {
                    formData.value[config] = response.data.data?.data || ''
                }
            }
        }
        
        // Load background image separately
        const bgResponse = await apiRequest('configurations/background_image')
        if (bgResponse.ok && bgResponse.data) {
            backgroundPreview.value = bgResponse.data ? `/storage/${bgResponse.data}` : ''
        }
    } catch (error) {
        console.error('Failed to load profile:', error)
        alertError('Gagal memuat data profil sekolah')
    } finally {
        loading.value = false
    }
}

// Handle logo file selection
const handleLogoChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Validate file
        if (!file.type.startsWith('image/')) {
            alertError('File harus berupa gambar')
            return
        }
        
        if (file.size > 2 * 1024 * 1024) { // 2MB
            alertError('Ukuran file maksimal 2MB')
            return
        }
        
        logoFile.value = file
        logoPreview.value = URL.createObjectURL(file)
    }
}

// Upload logo
const uploadLogo = async () => {
    if (!logoFile.value) return
    
    try {
        isUploading.value = true
        
        const formData = new FormData()
        formData.append('value', logoFile.value)
        formData.append('_method', 'PUT')
        
        const response = await fetch(`${import.meta.env.VITE_API_PATH}/api/v1/configurations/school_logo`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
            body: formData
        })
        
        if (response.ok) {
            alertSuccess('Logo berhasil diupload')
            logoFile.value = null
            // Reload profile to get new logo
            await loadProfile()
        } else {
            const error = await response.json()
            alertError(error.message || 'Gagal upload logo')
        }
    } catch (error) {
        console.error('Upload error:', error)
        alertError('Gagal upload logo')
    } finally {
        isUploading.value = false
    }
}

// Handle background image file selection
const handleBackgroundChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Validate file
        if (!file.type.startsWith('image/')) {
            alertError('File harus berupa gambar')
            return
        }
        
        if (file.size > 4 * 1024 * 1024) { // 4MB for background
            alertError('Ukuran file maksimal 4MB')
            return
        }
        
        backgroundFile.value = file
        backgroundPreview.value = URL.createObjectURL(file)
    }
}

// Upload background image
const uploadBackground = async () => {
    if (!backgroundFile.value) return
    
    try {
        isUploadingBackground.value = true
        
        const formData = new FormData()
        formData.append('background_image', backgroundFile.value)
        
        const response = await fetch(`${import.meta.env.VITE_API_PATH}/api/v1/configurations/background_image`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
            body: formData
        })
        
        if (response.ok) {
            alertSuccess('Background berhasil diupload')
            backgroundFile.value = null
            // Reload profile to get new background
            await loadProfile()
        } else {
            const error = await response.json()
            alertError(error.message || 'Gagal upload background')
        }
    } catch (error) {
        console.error('Upload error:', error)
        alertError('Gagal upload background')
    } finally {
        isUploadingBackground.value = false
    }
}

// Save profile
const saveProfile = async () => {
    try {
        saving.value = true
        
        if (!token) {
            alertError('User tidak terautentikasi')
            return
        }
        const originalData = {}
        const configs = ['school_name', 'school_address', 'school_phone', 'school_email']
        
        // Fetch current values first
        for (const config of configs) {
            const response = await apiRequest(`configurations/${config}`)
            console.log(`Fetching ${config}:`, response) // Debug log
            if (response.ok && response.data !== undefined) {
                originalData[config] = response.data
            }
        }
        
        // Update only changed fields
        const updatePromises = []
        
        if (formData.value?.school_name && formData.value.school_name !== (originalData.school_name || '')) {
            console.log(`Updating school_name from "${originalData.school_name}" to "${formData.value.school_name}"`)
            updatePromises.push(
                fetch(`${import.meta.env.VITE_API_PATH}/api/v1/configurations/school_name`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ value: formData.value.school_name })
                })
            )
        }
        
        if (formData.value?.school_address && formData.value.school_address !== (originalData.school_address || '')) {
            updatePromises.push(
                fetch(`${import.meta.env.VITE_API_PATH}/api/v1/configurations/school_address`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ value: formData.value.school_address })
                })
            )
        }
        
        if (formData.value?.school_phone && formData.value.school_phone !== (originalData.school_phone || '')) {
            updatePromises.push(
                fetch(`${import.meta.env.VITE_API_PATH}/api/v1/configurations/school_phone`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ value: formData.value.school_phone })
                })
            )
        }
        
        if (formData.value?.school_email && formData.value.school_email !== (originalData.school_email || '')) {
            updatePromises.push(
                fetch(`${import.meta.env.VITE_API_PATH}/api/v1/configurations/school_email`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ value: formData.value.school_email })
                })
            )
        }
        
        const results = await Promise.all(updatePromises)
        
        // Check if all updates were successful
        const allSuccessful = results.every(response => response.ok)
        if (allSuccessful) {
            alertSuccess('Profil sekolah berhasil diperbarui')
            
            // Reload topbar data by triggering storage event
            window.dispatchEvent(new Event('storage'))
            
            // Reload form data to get updated values
            await loadProfile()
        } else {
            alertError('Beberapa data gagal diperbarui')
        }
        
    } catch (error) {
        console.error('Save error:', error)
        alertError('Gagal memperbarui profil sekolah')
    } finally {
        saving.value = false
    }
}

// Computed properties
const hasChanges = computed(() => {
    return (formData.value?.school_name !== '') || 
           (formData.value?.school_address !== '') || 
           (formData.value?.school_phone !== '') || 
           (formData.value?.school_email !== '')
})

onMounted(() => {
    loadProfile()
})
</script>

<template>
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Profil Sekolah</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Main Form -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Sekolah</h3>
                        </div>
                        <div class="card-body">
                            <div v-if="loading" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Memuat data...</p>
                            </div>
                            
                            <form v-else @submit.prevent="saveProfile">
                                <!-- Nama Sekolah -->
                                <div class="form-group">
                                    <label for="school_name">
                                        <i class="fas fa-school"></i> Nama Sekolah
                                    </label>
                                    <input
                                        id="school_name"
                                        v-model="formData.school_name"
                                        type="text"
                                        class="form-control"
                                        placeholder="Masukkan nama sekolah"
                                        maxlength="255"
                                    />
                                </div>

                                <!-- Alamat Sekolah -->
                                <div class="form-group">
                                    <label for="school_address">
                                        <i class="fas fa-map-marker-alt"></i> Alamat Sekolah
                                    </label>
                                    <textarea
                                        id="school_address"
                                        v-model="formData.school_address"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Masukkan alamat sekolah"
                                        maxlength="500"
                                    ></textarea>
                                </div>

                                <!-- Telepon Sekolah -->
                                <div class="form-group">
                                    <label for="school_phone">
                                        <i class="fas fa-phone"></i> Telepon
                                    </label>
                                    <input
                                        id="school_phone"
                                        v-model="formData.school_phone"
                                        type="text"
                                        class="form-control"
                                        placeholder="Masukkan nomor telepon sekolah"
                                        maxlength="20"
                                    />
                                </div>

                                <!-- Email Sekolah -->
                                <div class="form-group">
                                    <label for="school_email">
                                        <i class="fas fa-envelope"></i> Email
                                    </label>
                                    <input
                                        id="school_email"
                                        v-model="formData.school_email"
                                        type="email"
                                        class="form-control"
                                        placeholder="Masukkan email sekolah"
                                        maxlength="255"
                                    />
                                </div>

                                <!-- Buttons -->
                                <div class="form-group">
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        :disabled="saving || !hasChanges"
                                    >
                                        <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                                        <i v-else class="fas fa-save mr-2"></i>
                                        {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Logo Upload -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Logo Sekolah</h3>
                        </div>
                        <div class="card-body text-center">
                            <!-- Logo Preview -->
                            <div class="logo-preview-container mb-3">
                                <img
                                    v-if="logoPreview"
                                    :src="logoPreview"
                                    alt="Logo Sekolah"
                                    class="logo-preview img-fluid"
                                />
                                <div v-else class="logo-placeholder">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                    <p class="text-muted mt-2">Belum ada logo</p>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <div class="form-group">
                                <input
                                    ref="logoInput"
                                    type="file"
                                    accept="image/*"
                                    @change="handleLogoChange"
                                    style="display: none"
                                />
                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-block"
                                    @click="$refs.logoInput.click()"
                                    :disabled="isUploading"
                                >
                                    <i v-if="isUploading" class="fas fa-spinner fa-spin mr-2"></i>
                                    <i v-else class="fas fa-upload mr-2"></i>
                                    {{ isUploading ? 'Mengupload...' : 'Pilih Logo' }}
                                </button>
                                
                                <button
                                    v-if="logoFile"
                                    type="button"
                                    class="btn btn-success btn-block mt-2"
                                    @click="uploadLogo"
                                    :disabled="isUploading"
                                >
                                    <i class="fas fa-check mr-2"></i>
                                    Upload Logo
                                </button>
                            </div>

                            <!-- Info -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <small>
                                    Format: JPEG, PNG, JPG, GIF<br>
                                    Ukuran maksimal: 2MB
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Background Image Upload -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Background</h3>
                        </div>
                        <div class="card-body text-center">
                            <!-- Background Preview -->
                            <div class="logo-preview-container mb-3">
                                <img
                                    v-if="backgroundPreview"
                                    :src="backgroundPreview"
                                    alt="Background Sekolah"
                                    class="logo-preview img-fluid"
                                />
                                <div v-else class="logo-placeholder">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                    <p class="text-muted mt-2">Belum ada background</p>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <div class="form-group">
                                <input
                                    ref="backgroundInput"
                                    type="file"
                                    accept="image/*"
                                    @change="handleBackgroundChange"
                                    style="display: none"
                                />
                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-block"
                                    @click="$refs.backgroundInput.click()"
                                    :disabled="isUploadingBackground"
                                >
                                    <i v-if="isUploadingBackground" class="fas fa-spinner fa-spin mr-2"></i>
                                    <i v-else class="fas fa-upload mr-2"></i>
                                    {{ isUploadingBackground ? 'Mengupload...' : 'Pilih Background' }}
                                </button>
                                
                                <button
                                    v-if="backgroundFile"
                                    type="button"
                                    class="btn btn-success btn-block mt-2"
                                    @click="uploadBackground"
                                    :disabled="isUploadingBackground"
                                >
                                    <i class="fas fa-check mr-2"></i>
                                    Upload Background
                                </button>
                            </div>

                            <!-- Info -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <small>
                                    Format: JPEG, PNG, JPG, GIF<br>
                                    Ukuran maksimal: 4MB
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.logo-preview-container {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    background-color: #f8f9fa;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-preview {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.logo-placeholder {
    text-align: center;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: block;
}

.form-control {
    border-radius: 6px;
    border: 1px solid #ced4da;
    padding: 0.75rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    border-color: #001f3f;
    box-shadow: 0 0 0 0.2rem rgba(0, 31, 63, 0.25);
}

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
    border-radius: 8px;
}

.card-header {
    background-color: #001f3f;
    color: white;
    border-radius: 8px 8px 0 0 !important;
}

.btn-primary {
    background-color: #001f3f;
    border-color: #001f3f;
}

.btn-primary:hover {
    background-color: #003366;
    border-color: #003366;
}

.btn-primary:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
}

@media (max-width: 768px) {
    .col-md-8, .col-md-4 {
        margin-bottom: 1rem;
    }
}
</style>
