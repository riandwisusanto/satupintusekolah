<script setup>
import { ref, computed } from 'vue'
import SelectServerSide from '@/components/SelectServerSide.vue'

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({})
    },
    showTeacherFilter: {
        type: Boolean,
        default: true
    },
    showAcademicYearFilter: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits(['update:modelValue', 'apply', 'reset'])

const filters = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
})

const filterType = ref('date_range') // 'date_range', 'month', 'academic_year'

const applyFilters = () => {
    emit('apply', filters.value)
}

const resetFilters = () => {
    filters.value = {
        start_date: '',
        end_date: '',
        month: '',
        academic_year_id: '',
        teacher_id: ''
    }
    filterType.value = 'date_range'
    emit('reset')
}
</script>

<template>
    <div class="card card-outline card-navy shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-serif-formal">
                <i class="fas fa-filter mr-2"></i>
                Filter Laporan
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Filter Type Selection -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Tipe Filter Periode</label>
                    <div class="btn-group btn-group-toggle w-100" role="group">
                        <button 
                            type="button" 
                            class="btn"
                            :class="filterType === 'date_range' ? 'btn-navy' : 'btn-outline-secondary'"
                            @click="filterType = 'date_range'"
                        >
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Range Tanggal
                        </button>
                        <button 
                            type="button" 
                            class="btn"
                            :class="filterType === 'month' ? 'btn-navy' : 'btn-outline-secondary'"
                            @click="filterType = 'month'"
                        >
                            <i class="fas fa-calendar mr-1"></i>
                            Bulanan
                        </button>
                        <button 
                            v-if="showAcademicYearFilter"
                            type="button" 
                            class="btn"
                            :class="filterType === 'academic_year' ? 'btn-navy' : 'btn-outline-secondary'"
                            @click="filterType = 'academic_year'"
                        >
                            <i class="fas fa-graduation-cap mr-1"></i>
                            Tahun Ajaran
                        </button>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <template v-if="filterType === 'date_range'">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">
                                <i class="fas fa-calendar-day mr-1"></i>
                                Tanggal Mulai
                            </label>
                            <input 
                                id="start_date"
                                v-model="filters.start_date" 
                                type="date" 
                                class="form-control"
                            />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">
                                <i class="fas fa-calendar-day mr-1"></i>
                                Tanggal Akhir
                            </label>
                            <input 
                                id="end_date"
                                v-model="filters.end_date" 
                                type="date" 
                                class="form-control"
                            />
                        </div>
                    </div>
                </template>

                <!-- Month Filter -->
                <div v-if="filterType === 'month'" class="col-md-12">
                    <div class="form-group">
                        <label for="month">
                            <i class="fas fa-calendar mr-1"></i>
                            Pilih Bulan
                        </label>
                        <input 
                            id="month"
                            v-model="filters.month" 
                            type="month" 
                            class="form-control"
                        />
                    </div>
                </div>

                <!-- Academic Year Filter -->
                <div v-if="filterType === 'academic_year' && showAcademicYearFilter" class="col-md-12">
                    <SelectServerSide
                        v-model="filters.academic_year_id"
                        label="Tahun Ajaran"
                        name="academic_year_id"
                        placeholder="Pilih Tahun Ajaran"
                        :serverside="true"
                        endpoint="academic-years"
                        label-key="name"
                        value-key="id"
                    />
                </div>

                <!-- Teacher Filter -->
                <div v-if="showTeacherFilter" class="col-md-12">
                    <SelectServerSide
                        v-model="filters.teacher_id"
                        label="Filter Guru (Opsional)"
                        name="teacher_id"
                        placeholder="Semua Guru"
                        :serverside="true"
                        endpoint="users?filter[role_id]=2"
                        label-key="name"
                        value-key="id"
                    />
                </div>

                <!-- Action Buttons -->
                <div class="col-md-12">
                    <button 
                        type="button" 
                        class="btn btn-navy shadow-sm"
                        @click="applyFilters"
                    >
                        <i class="fas fa-search mr-2"></i>
                        Tampilkan Laporan
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-secondary ml-2"
                        @click="resetFilters"
                    >
                        <i class="fas fa-redo mr-2"></i>
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn-navy {
    background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
    border: none;
    color: white;
}

.btn-navy:hover {
    background: linear-gradient(135deg, #003366 0%, #004080 100%);
}

.btn-group-toggle .btn {
    transition: all 0.3s ease;
}

.form-control {
    border-radius: 6px;
}
</style>
