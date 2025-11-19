<template>
  <span
    v-if="resolved"
    class="badge"
    :class="resolved.class"
    :style="resolved.style"
  >
    {{ resolved.text }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * StatusTag – badge fleksibel untuk kolom status (Bootstrap/ AdminLTE)
 *
 * Props:
 *  @prop {String|Number} status   – nilai status (wajib)
 *  @prop {Object} map             – optional mapping status ➜ { text, class, style }
 *                                    contoh { paid: { text: 'Paid', class: 'badge-success' } }
 *  @prop {String} color           – override class badge-*
 *  @prop {String} label           – override label teks
 */
const props = defineProps({
  status: { type: [String, Number], required: true },
  map: { type: Object, default: () => ({}) },
  color: String,
  label: String
});

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

const resolved = computed(() => {
  const key = String(props.status);
  const fromMap = props.map[key] || {};
  // prioritas: explicit prop > map > default
  const text = props.label || fromMap.text || capitalize(key);
  const badgeClass = props.color || fromMap.class || 'badge-secondary';
  const style = fromMap.style || undefined; // inline style if needed
  return { text, class: badgeClass, style };
});
</script>