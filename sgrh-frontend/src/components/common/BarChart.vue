<template>
  <div class="space-y-2">
    <div
      v-for="item in items"
      :key="item.label"
      class="flex items-center gap-3"
    >
      <span class="text-xs text-gray-600 w-28 shrink-0 truncate" :title="item.label">
        {{ item.label }}
      </span>
      <div class="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
        <div
          class="h-5 rounded-full flex items-center justify-end pr-2 text-xs text-white font-medium transition-all duration-500"
          :style="{
            width: maxVal > 0 ? Math.max(5, (item.value / maxVal) * 100) + '%' : '5%',
            backgroundColor: item.color || defaultColor,
          }"
        >
          {{ item.value }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  items:        { type: Array,  required: true }, // [{ label, value, color }]
  defaultColor: { type: String, default: '#2e75b6' },
})

const maxVal = computed(() => Math.max(...props.items.map(i => i.value), 1))
</script>