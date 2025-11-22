<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-serif-formal">Profile Saya</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Profile Card -->
                <div class="col-md-4">
                    <div class="card card-outline card-navy shadow-sm">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img
                                    :src="user.user?.photo || '/assets/images/avatar5.png'"
                                    class="profile-user-img img-fluid img-circle"
                                    alt="User Profile Picture"
                                    style="width: 100px; height: 100px; object-fit: cover;"
                                />
                            </div>
                            <h3 class="profile-username text-center">{{ user.user?.name }}</h3>
                            <p class="text-muted text-center">{{ user.user?.role?.label }}</p>
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Email</b> <a class="float-right">{{ user.user?.email }}</a>
                                </li>
                                <li class="list-group-item" v-if="user.user?.nip">
                                    <b>NIP</b> <a class="float-right">{{ user.user?.nip }}</a>
                                </li>
                                <li class="list-group-item" v-if="user.user?.phone">
                                    <b>Telepon</b> <a class="float-right">{{ user.user?.phone }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="col-md-8">
                    <div class="card card-outline card-navy shadow-sm">
                        <div class="card-header border-bottom-0">
                            <h3 class="card-title font-serif-formal">Edit Profile</h3>
                        </div>
                        <form @submit.prevent="updateProfile">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="name"
                                                v-model="form.name"
                                                required
                                                :class="{ 'is-invalid': errors.name }"
                                            />
                                            <div class="invalid-feedback" v-if="errors.name">
                                                {{ errors.name[0] }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <input
                                                type="email"
                                                class="form-control"
                                                id="email"
                                                v-model="form.email"
                                                @input="preventEmailChange"
                                                required
                                                :class="{ 'is-invalid': errors.email }"
                                            />
                                            <small class="form-text text-muted">Email tidak dapat diubah</small>
                                            <div class="invalid-feedback" v-if="errors.email">
                                                {{ errors.email[0] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nip">NIP</label>
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="nip"
                                                disabled
                                                v-model="form.nip"
                                                :class="{ 'is-invalid': errors.nip }"
                                            />
                                            <div class="invalid-feedback" v-if="errors.nip">
                                                {{ errors.nip[0] }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">No. Telepon</label>
                                            <input
                                                type="tel"
                                                class="form-control"
                                                id="phone"
                                                v-model="form.phone"
                                                :class="{ 'is-invalid': errors.phone }"
                                            />
                                            <div class="invalid-feedback" v-if="errors.phone">
                                                {{ errors.phone[0] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password Baru</label>
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password"
                                                v-model="form.password"
                                                placeholder="Kosongkan jika tidak ingin mengubah password"
                                                :class="{ 'is-invalid': errors.password }"
                                            />
                                            <div class="invalid-feedback" v-if="errors.password">
                                                {{ errors.password[0] }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_confirmation">Konfirmasi Password Baru</label>
                                            <input
                                                type="password"
                                                class="form-control"
                                                id="password_confirmation"
                                                v-model="form.password_confirmation"
                                                placeholder="Ulangi password baru"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <FileUpload 
                                    v-model="form.photo" 
                                    label="Foto Profile" 
                                    accept="jpeg,jpg,png"
                                    class="mt-3"
                                />
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary" :disabled="loading">
                                    <i class="fas fa-save mr-1"></i>
                                    {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-default ml-2"
                                    @click="resetForm"
                                    :disabled="loading"
                                >
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useUser } from '@/store'
import { apiRequest } from '@/lib/apiClient'
import { alertSuccess, alertError } from '@/lib/alert'
import FileUpload from '@/components/FileUpload.vue'

const { user, setUser } = useUser()

const loading = ref(false)
const errors = ref({})

const form = reactive({
    name: '',
    email: '',
    nip: '',
    phone: '',
    password: '',
    password_confirmation: '',
    photo: null
})

async function loadProfile() {
    try {
        const { ok, data } = await apiRequest('me')
        if (ok) {
            const userData = data.data.user
            Object.assign(form, {
                name: userData.name || '',
                email: userData.email || '',
                nip: userData.nip || '',
                phone: userData.phone || '',
                photo: userData.photo || null
            })
        }
    } catch (error) {
        await alertError('Gagal memuat data profile')
    }
}

function buildPayload(form) {
    const hasFile = form.photo instanceof File

    if (!hasFile) {
        // Remove photo from payload if it's not a file, but include all other fields
        const { photo, ...payload } = form
        return {
            type: 'json',
            body: {
                ...payload,
            },
        }
    }

    const fd = new FormData()
    Object.keys(form).forEach((key) => {
        if (key !== 'password_confirmation' && form[key] !== null) {
            fd.append(key, form[key])
        }
    })
    fd.append('_method', 'PATCH')

    return { type: 'formdata', body: fd }
}

async function updateProfile() {
    loading.value = true
    errors.value = {}
    
    try {
        const { type, body } = buildPayload(form)

        const { ok, data, error } = await apiRequest('me', {
            method: type === 'json' ? 'PATCH' : 'POST',
            body,
            headers: type === 'json' ? {} : { 'Content-Type': 'multipart/form-data' },
        })

        if (ok) {
            await alertSuccess('Profile berhasil diperbarui')
            
            // Reload profile data
            window.location.reload()
        } else {
            if (error && typeof error === 'object') {
                errors.value = error
            } else {
                await alertError(error || 'Gagal memperbarui profile')
            }
        }
    } catch (error) {
        await alertError('Terjadi kesalahan saat memperbarui profile')
    } finally {
        loading.value = false
    }
}


function resetForm() {
    loadProfile()
    errors.value = {}
}

onMounted(() => {
    loadProfile()
})
</script>

<style scoped>
</style>
