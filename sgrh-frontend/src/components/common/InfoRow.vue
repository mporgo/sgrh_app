<!-- src/components/common/InfoRow.vue -->
<template>
  <div class="flex justify-between text-sm">
    <span class="text-gray-500 font-medium">{{ label }}</span>
    <span class="text-gray-800 text-right">{{ value ?? '—' }}</span>
  </div>
</template>

<script setup>
defineProps({ label: String, value: [String, Number] })
</script>