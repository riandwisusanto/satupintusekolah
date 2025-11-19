<template>
    <div class="file-upload">
        <input
            :id="id"
            ref="fileInput"
            type="file"
            class="hidden"
            accept="image/jpeg,image/png,image/jpg,application/pdf"
            :required="required && !fileUrl"
            :disabled="disabled"
            @change="onFileChange"
            @invalid="onInvalid"
        />
        <slot :id="id" name="trigger" :open="openPicker">
            <label
                :for="id"
                class="cursor-pointer px-4 border rounded bg-white-100 hover:bg-white-200"
            >
                {{ label }}
                <span v-if="required" class="text-red-500">*</span>
            </label>
        </slot>

        <slot name="preview" :file-url="fileUrl" :file-name="fileName" :is-image="isImage">
            <div v-if="fileUrl" class="mt-2">
                <img
                    v-if="isImage"
                    :src="fileUrl"
                    alt="preview"
                    class="w-24 h-24 object-cover rounded border cursor-pointer"
                    @click.stop="openPreview"
                />
                <div
                    v-else-if="isPdf"
                    class="w-24 h-24 flex items-center justify-center border rounded bg-gray-50 cursor-pointer"
                    @click="openPreview"
                >
                    <span class="text-sm text-gray-600">PDF</span>
                </div>
                <a v-else :href="fileUrl" target="_blank" class="text-blue-600 underline">
                    {{ fileName }}
                </a>
            </div>
        </slot>

        <!-- Error -->
        <p v-if="errorMessage" class="text-red-500 text-sm mt-1">
            {{ errorMessage }}
        </p>

        <transition name="fade">
            <div v-if="showPreview" class="fullscreen-overlay" @click.self="closePreview">
                <div class="fullscreen-content">
                    <button
                        type="button"
                        class="btn btn-sm btn-light close-btn"
                        @click="closePreview"
                    >
                        ✕
                    </button>

                    <!-- Gambar -->
                    <img
                        v-if="isImage"
                        :src="fileUrl"
                        alt="fullscreen-preview"
                        class="img-fluid"
                        style="max-height: 90vh; object-fit: contain"
                    />

                    <!-- PDF pakai iframe -->
                    <iframe v-else-if="isPdf" :src="fileUrl" class="pdf-frame"></iframe>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    modelValue: {
        type: [File, String, null],
        default: null,
    },
    label: {
        type: String,
        default: 'Upload File',
    },
    id: {
        type: String,
        default: () => `file-input-${Math.random().toString(36).substr(2, 9)}`,
    },
    required: {
        type: Boolean,
        default: false,
    },
    accept: {
        type: String,
        default: 'jpeg,jpg,png,pdf',
    },
    allowPreview: {
        type: Boolean,
        default: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const errorMessage = ref('')
const showPreview = ref(false)
const pdfCanvas = ref(null)

const isFileObject = (val) => typeof File !== 'undefined' && val instanceof File

const fileUrl = computed(() => {
    if (!props.modelValue) return null
    if (isFileObject(props.modelValue)) return URL.createObjectURL(props.modelValue)
    return props.modelValue
})

const fileName = computed(() => {
    if (!props.modelValue) return null
    if (isFileObject(props.modelValue)) return props.modelValue.name
    return props.modelValue.split('/').pop()
})

const isImage = computed(() => {
    if (!props.modelValue) return false
    if (isFileObject(props.modelValue)) return props.modelValue.type.startsWith('image/')
    return /\.(png|jpe?g|gif|webp)$/i.test(props.modelValue)
})

const isPdf = computed(() => {
    if (!props.modelValue) return false
    if (isFileObject(props.modelValue)) return props.modelValue.type === 'application/pdf'
    return /\.pdf$/i.test(props.modelValue)
})

const onFileChange = (e) => {
    const file = e.target.files?.[0] || null

    // PDF size check
    if (file && file.type === 'application/pdf' && file.size > 10 * 1024 * 1024) {
        errorMessage.value = 'Ukuran file PDF tidak boleh lebih dari 10MB'
        return
    }

    emit('update:modelValue', file)
    errorMessage.value = ''
}

const onInvalid = (e) => {
    errorMessage.value = e.target.validationMessage
}

watch(
    () => props.modelValue,
    (val) => {
        if (!fileInput.value) return
        if (props.required && !val) {
            fileInput.value.setCustomValidity('File wajib diupload')
        } else {
            fileInput.value.setCustomValidity('')
        }
    },
    { immediate: true }
)

const openPicker = () => {
    if (props.disabled) return
    fileInput.value?.click()
}

const openPreview = async () => {
    showPreview.value = true
}

const closePreview = () => {
    showPreview.value = false
}
</script>

<style scoped>
.file-upload {
    display: flex;
    flex-direction: column;
}

.fullscreen-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.fullscreen-content {
    position: relative;
    background: #fff;
    padding: 10px;
    border-radius: 8px;
    max-height: 90vh;
}

.close-btn {
    position: absolute;
    top: 8px;
    right: 8px;
}
.pdf-frame {
    width: 80vw; /* Lebar 80% viewport */
    height: 90vh; /* Tinggi 90% viewport */
    border: none;
    background: #fff;
    border-radius: 8px;
}
</style>
