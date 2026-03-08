<template>
  <div class="relative">
    <svg :viewBox="`0 0 ${width} ${height}`" class="w-full" :style="{ height: svgHeight + 'px' }">

      <!-- Grille -->
      <g v-for="i in 5" :key="i">
        <line
          :x1="padding" :y1="padding + ((i-1) / 4) * chartH"
          :x2="width - padding" :y2="padding + ((i-1) / 4) * chartH"
          stroke="#e5e7eb" stroke-width="1"
        />
        <text
          :x="padding - 5" :y="padding + ((i-1) / 4) * chartH + 4"
          text-anchor="end" font-size="10" fill="#9ca3af"
        >
          {{ Math.round(maxVal - ((i-1) / 4) * maxVal) }}
        </text>
      </g>

      <!-- Ligne de données -->
      <polyline
        v-if="points.length > 1"
        :points="pointsStr"
        fill="none" stroke="#2e75b6" stroke-width="2"
        stroke-linejoin="round" stroke-linecap="round"
      />

      <!-- Zone remplie -->
      <polygon
        v-if="points.length > 1"
        :points="areaStr"
        fill="#2e75b6" fill-opacity="0.1"
      />

      <!-- Points -->
      <g v-for="(pt, i) in points" :key="i">
        <circle :cx="pt.x" :cy="pt.y" r="4" fill="#2e75b6" />
        <text :x="pt.x" :y="height - 5" text-anchor="middle" font-size="10" fill="#6b7280">
          {{ labels[i] }}
        </text>
      </g>
    </svg>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data:      { type: Array,  required: true },   // [valeur1, valeur2, ...]
  labels:    { type: Array,  required: true },   // ['Jan', 'Fév', ...]
  svgHeight: { type: Number, default: 180 },
})

const width   = 400
const height  = 200
const padding = 40
const chartW  = computed(() => width - padding * 2)
const chartH  = computed(() => height - padding * 2)
const maxVal  = computed(() => Math.max(...props.data, 1))

const points = computed(() =>
  props.data.map((val, i) => ({
    x: padding + (i / Math.max(props.data.length - 1, 1)) * chartW.value,
    y: padding + (1 - val / maxVal.value) * chartH.value,
  }))
)

const pointsStr = computed(() =>
  points.value.map(p => `${p.x},${p.y}`).join(' ')
)

const areaStr = computed(() => {
  const pts = points.value.map(p => `${p.x},${p.y}`).join(' ')
  const last = points.value[points.value.length - 1]
  const first = points.value[0]
  return `${pts} ${last?.x},${height - padding} ${first?.x},${height - padding}`
})
</script>