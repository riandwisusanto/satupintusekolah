<script setup>
import { h, ref } from 'vue'
import Form from './form.vue'

const tableRef = ref()
const loading = ref(false)
const selected = ref(null)
const drawer = ref(false)

const columns = [
    { field: 'id', display: 'ID' },
    { field: 'event', display: 'Event' },
    { field: 'user.name', display: 'User' },
    {
        field: 'model_type',
        display: 'Model',
        component: ({ row }) => h('span', {}, row.model_type),
    },
    {
        field: 'created_at',
        display: 'Created At',
        component: ({ row }) => h('span', {}, new Date(row.created_at).toLocaleString()),
    },
    {
        field: 'updated_at',
        display: 'Updated At',
        component: ({ row }) => h('span', {}, new Date(row.updated_at).toLocaleString()),
    },
]

const onRowClick = (row) => {
    // console.log(row);
    selected.value = row
    drawer.value = true
}
</script>

<template>
    <section class="content" style="height: calc(100vh - 100px); overflow: auto">
        <div class="container-fluid h-100">
            <TableServerSide
                ref="tableRef"
                title="Model Logs"
                class="mx-auto h-100"
                :columns="columns"
                :initial-sort="{ field: 'id', order: 'desc' }"
                :per_page="10"
                :loading="loading"
                endpoint="model-logs"
                :extra="{
                    with: 'user,model',
                }"
                @row-click="onRowClick"
            />
        </div>
    </section>

    <Form v-model="drawer" :log="selected" type="model" />
</template>
