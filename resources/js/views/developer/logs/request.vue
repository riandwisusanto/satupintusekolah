<script setup>
import StatusTag from '@/components/StatusTag.vue'
import { h, ref } from 'vue'
import Form from './form.vue'
import { useGlobalComponent } from '@/lib/useGlobalComponent'
const TruncateTooltip = useGlobalComponent('TruncateTooltip')

const tableRef = ref()
const loading = ref(false)
const selected = ref(null)
const drawer = ref(false)

const columns = [
    { field: 'id', display: 'ID' },
    { field: 'method', display: 'Method' },
    {
        field: 'url',
        display: 'Endpoint',
        component: ({ row }) => {
            return h('span', { class: 'text-black' }, row.url.split('v1')[1])
        },
    },
    { field: 'ip', display: 'IP' },
    { field: 'user.name', display: 'User' },
    {
        field: 'payload',
        display: 'Payload',
        component: ({ row }) => {
            const text = row.payload ? JSON.stringify(row.payload) : '-'
            return h(TruncateTooltip, { text, limit: 50 })
        },
    },
    {
        field: 'response_code',
        display: 'Code',
        component: ({ row }) => {
            return h(StatusTag, {
                status: row.response_code,
                class: row.response_code >= 400 ? 'badge-danger' : 'badge-success',
            })
        },
    },
    {
        field: 'response_body',
        display: 'Response Body',
        component: ({ row }) => {
            const text = row.response_body ? JSON.stringify(row.response_body) : '-'
            return h(TruncateTooltip, { text, limit: 100 })
        },
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
    selected.value = row
    drawer.value = true
}
</script>

<template>
    <section class="content" style="height: calc(100vh - 100px); overflow: auto">
        <div class="container-fluid h-100">
            <TableServerSide
                ref="tableRef"
                title="Request Logs"
                class="mx-auto h-100"
                :columns="columns"
                :initial-sort="{ field: 'id', order: 'desc' }"
                :per_page="10"
                :loading="loading"
                endpoint="request-logs"
                :extra="{
                    with: 'user',
                }"
                @row-click="onRowClick"
            />
        </div>
    </section>

    <Form v-model="drawer" :log="selected" type="request" />
</template>
