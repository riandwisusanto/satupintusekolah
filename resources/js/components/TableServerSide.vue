<template>
  <div class="card card-outline card-navy position-relative shadow-sm">
    <!-- Overlay Loading -->
    <div v-if="loading" class="overlay d-flex justify-content-center align-items-center">
      <i class="fas fa-2x fa-sync-alt fa-spin"></i>
    </div>

    <!-- Header with Search and Action Buttons -->
    <div class="card-header p-2 d-flex justify-content-between align-items-center flex-wrap border-bottom-0">
      <div class="d-flex align-items-center mb-2 mb-md-0 gap-2">
        <h5 class="font-serif-formal mb-0">{{ props.title }}</h5>
        <router-link v-if="props.showAddButton && props.addRoute" :to="props.addRoute" class="btn btn-navy btn-sm me-2 shadow-sm">
          <i class="fas fa-plus me-1"></i> Tambah Baru
        </router-link>
        <button v-if="props.showAddButton && props.showDrawer" class="btn btn-navy btn-sm me-2 shadow-sm" @click="openDrawer">
          <i class="fas fa-plus me-1"></i> Tambah Baru
        </button>
        <button v-if="props.showExportButton" class="btn btn-success btn-sm shadow-sm" @click="onExport" :disabled="loadingExport">
          <i class="fas fa-file-export mr-1"></i> Export Data
        </button>
      </div>

      <div class="input-group ms-auto" style="width: 180px;">
        <input v-model="search" type="text" class="form-control float-right" placeholder="Search" />
        <div class="input-group-append">
          <button class="btn btn-default" @click="reload">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Table Content -->
    <div class="card-body table-responsive p-0">
      <table class="table table-hover ledger-table text-nowrap mb-0">
        <thead>
          <tr>
            <th
              v-for="(col, i) in columns"
              :key="i"
              @click="col.sortable !== false && sort(col.field)"
              :style="{ cursor: col.sortable === false ? 'default' : 'pointer', minWidth: col.minWidth ?? '' }"
            >
              <slot :name="`header-${col.field}`" :col="col">
                <component v-if="isComponent(col.display)" :is="col.display" />
                <span v-else>{{ col.display }}</span>
              </slot>
              <span v-if="sortField === col.field && col.sortable !== false">
                {{ sortOrder === 'asc' ? '▲' : '▼' }}
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, i) in rows"
            :key="row[idKey]"
            @click="onRowClick(row, i)"
            style="cursor: pointer"
          >
            <td v-for="col in columns" :key="col.field">
              <slot :name="`cell-${col.field}`" :row="row" :col="col" :index="i">
                <component
                  v-if="isComponent(col.component)"
                  :is="col.component"
                  :row="row"
                  :index="i"
                  :value="getDeepValue(row, col.field)"
                />
                <span v-else>{{ getDeepValue(row, col.field) }}</span>
              </slot>
            </td>
          </tr>
          <tr v-if="rows.length === 0">
            <td :colspan="columns.length" class="text-center">Data Kosong</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="card-footer clearfix">
      <div class="row">
        <div class="col-md-1 col-sm-1 col-4">
          <select class="form-control form-control-sm" v-model="perPage">
            <option value="all">All</option>
            <option v-for="n in [2, 10, 15, 50, 100, 200]" :key="n" :value="n">{{ n }}</option>
          </select>
        </div>
        <div class="col-md-5 col-sm-5 col-8">
          <p class="float-right float-sm-left">{{ startRecord }} - {{ endRecord }} dari {{ totalRows }} data</p>
        </div>
        <div class="col-md-6 col-sm-6 col-12">
          <div class="d-flex justify-content-end w-100 w-md-auto">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button class="page-link" @click="goToPage(currentPage - 1)">«</button>
              </li>
              <li
                class="page-item"
                v-for="page in visiblePages"
                :key="page"
                :class="{ active: currentPage === page }"
              >
                <button class="page-link" @click="goToPage(page)">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button class="page-link" @click="goToPage(currentPage + 1)">»</button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <slot name="footer" />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeMount } from 'vue';
import { useUser } from '../store';

const props = defineProps({
  title: { type: String, default: 'Table' },
  serverside: { type: Boolean, default: true },
  showAddButton: { type: Boolean, default: false },
  addRoute: { type: String, default: '' },
  showDrawer: { type: Boolean, default: false },
  showExportButton: { type: Boolean, default: false },
  loadingExport: { type: Boolean, default: false },
  columns: { type: Array, required: true },
  initialSort: { type: Object, default: () => ({ field: null, order: 'asc' }) },
  endpoint: { type: String, default: '' },
  per_page: { type: Number, default: 10 },
  extra: { type: Object, default: () => ({}) },
  idKey: { type: String, default: 'id' },
  rows: { type: Array, default: () => [] },
});

const emit = defineEmits(['row-click', 'export', 'initial-load', 'open-drawer']);

const isComponent = val => val && (typeof val === 'object' || typeof val === 'function');
const getDeepValue = (obj, path) => path.split('.').reduce((acc, key) => acc?.[key], obj);

const search = ref('');
const currentPage = ref(1);
const perPage = ref(props.per_page);
const sortField = ref(props.initialSort.field);
const sortOrder = ref(props.initialSort.order);
const remoteRows = ref([]);
const loading = ref(false);
const remoteTotal = ref(0);

const totalRows = computed(() => (props.serverside ? remoteTotal.value : props.rows.length));
const perPageNum = computed(() => (perPage.value === 'all' ? totalRows.value || 1 : Number(perPage.value)));
const totalPages = computed(() => Math.max(1, Math.ceil(totalRows.value / perPageNum.value)));
const startRecord = computed(() => (totalRows.value === 0 ? 0 : (currentPage.value - 1) * perPageNum.value + 1));
const endRecord = computed(() => Math.min(startRecord.value + perPageNum.value - 1, totalRows.value));

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, start + 4)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const rows = computed(() => {
  if (props.serverside) return remoteRows.value;

  let list = [...props.rows];
  if (search.value) {
    const lower = search.value.toLowerCase();
    list = list.filter(r => Object.values(r).some(v => String(v).toLowerCase().includes(lower)));
  }

  if (sortField.value) {
    list.sort((a, b) => {
      const A = getDeepValue(a, sortField.value);
      const B = getDeepValue(b, sortField.value);
      if (A < B) return sortOrder.value === 'asc' ? -1 : 1;
      if (A > B) return sortOrder.value === 'asc' ? 1 : -1;
      return 0;
    });
  }

  return perPage.value === 'all' ? list : list.slice(startRecord.value - 1, startRecord.value - 1 + perPageNum.value);
});

const user = useUser();
const token = user.user.accesstoken || '';

async function fetchData() {
  if (!props.serverside) return;
  loading.value = true;

  const params = new URLSearchParams({
    page: currentPage.value,
    per_page: perPage.value,
    search: search.value,
    sort: (sortOrder.value === 'asc' ? '' : '-') + sortField.value,
    ...props.extra,
  });

  if (perPage.value === 'all') {
    params.delete('per_page')
    params.append('all', 'true')
  };

  try {
    const res = await fetch(`${import.meta.env.VITE_API_PATH}/api/v1/${props.endpoint}?${params}`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    remoteRows.value = json.data;
    remoteTotal.value = json.meta.total || 0;
  } catch (e) {
    console.error('Fetch table failed', e);
    remoteRows.value = [];
    remoteTotal.value = 0;
  }
  loading.value = false;
}

function reload() {
  currentPage.value = 1;
  if (props.serverside) {
    fetchData();
  }
  // For local mode, the computed properties will automatically update
}

function sort(field) {
  if (sortField.value === field) sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  else {
    sortField.value = field;
    sortOrder.value = 'asc';
  }
  if (props.serverside) {
    fetchData();
  }
  // For local mode, the computed properties will automatically update
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  if (props.serverside) {
    fetchData();
  }
  // For local mode, the computed properties will automatically update
}

function onRowClick(row, idKey) {
  emit('row-click', row, idKey);
}

function onExport(row) {
  emit('export', row);
}

function openDrawer() {
  emit('open-drawer', true)
}
/* ------------- watchers ------------- */
let debounceTimer
watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => reload(), 400);
});
watch(perPage, reload);
watch(() => props.endpoint, reload);
watch(() => props.extra, reload, { deep: true });

onMounted(async () => {
  if (props.serverside) {
    emit('initial-load', fetchData);
  } else {
    fetchData();
  }
});

onMounted(() => fetchData())
onBeforeMount(() => clearTimeout(debounceTimer))

defineExpose({ reload })
</script>

<style scoped>
.table th { user-select: none; }
.overlay { position: absolute; inset: 0; background: rgba(255, 255, 255, 0.55); z-index: 10; }
</style>
