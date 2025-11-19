<script setup>
import Tab from '../../components/Tab.vue'
import Tabs from '../../components/Tabs.vue'

import { defineAsyncComponent } from 'vue'

const RequestLogs = defineAsyncComponent(() => import('./logs/request.vue'))
const ModelLogs = defineAsyncComponent(() => import('./logs/model.vue'))
</script>

<template>
    <section class="content-header">
        <h5 class="text-center">Developer</h5>
    </section>
    <section class="content">
        <Tabs>
            <Tab name="request" label="Request Logs" icon="bi bi-clock-history">
                <RequestLogs />
            </Tab>
            <Tab name="model" label="Model Logs" icon="bi bi-server">
                <ModelLogs />
            </Tab>
        </Tabs>
    </section>
</template>
