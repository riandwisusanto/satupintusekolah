<template>
  <div>
    <ul class="nav nav-tabs tab-custom-style">
      <li class="nav-item" v-for="tab in tabList" :key="tab.name">
        <a
          href="#"
          class="nav-link"
          :class="{ active: tab.name === activeTab }"
          @click.prevent="setActiveTab(tab.name)"
        >
          <i v-if="tab.icon" :class="`${tab.icon} me-1`"></i>
          {{ tab.label }}
        </a>
      </li>
    </ul>

    <transition name="fade-tab" mode="out-in">
      <div class="tab-content p-4 border border-top-0 rounded-bottom bg-white shadow-sm" :key="activeTab">
        <slot />
      </div>
    </transition>
  </div>
</template>

<script setup>
import { provide, ref } from 'vue'

const activeTab = ref('')
const tabList = ref([])

function setActiveTab(name) {
  activeTab.value = name
}

function registerTab(tab) {
  const alreadyExists = tabList.value.some(t => t.name === tab.name);
  if (!alreadyExists) {
    tabList.value.push(tab);
    if (!activeTab.value) activeTab.value = tab.name;
  }
}

provide('activeTab', activeTab)
provide('registerTab', registerTab)
provide('setActiveTab', setActiveTab)
</script>

<style scoped>
.tab-custom-style .nav-link {
  color: #198754;
  font-weight: 500;
  transition: all 0.3s ease;
  border-radius: 0.5rem 0.5rem 0 0;
  margin-right: 4px;
}
.tab-custom-style .nav-link:hover {
  background-color: #eaf5ef;
  color: #157347;
}
.tab-custom-style .nav-link.active {
  background-color: #ffffff;
  color: #198754;
  font-weight: 600;
  box-shadow: inset 0 -2px 0 #198754;
  border-color: #dee2e6 #dee2e6 #ffffff;
}
.fade-tab-enter-active,
.fade-tab-leave-active {
  transition: opacity 0.3s ease;
}
.fade-tab-enter-from,
.fade-tab-leave-to {
  opacity: 0;
}
</style>
