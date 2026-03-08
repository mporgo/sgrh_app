<template>
  <div class="overflow-x-auto rounded-xl border border-gray-200">
    <table class="w-full text-sm text-left">
      <thead class="bg-primary-700 text-white">
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            class="px-4 py-3 font-semibold whitespace-nowrap"
          >
            {{ col.label }}
          </th>
          <th v-if="$slots.actions" class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td :colspan="columns.length + 1" class="text-center py-12">
            <div class="flex justify-center">
              <div class="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin" />
            </div>
          </td>
        </tr>
        <tr v-else-if="!data.length">
          <td :colspan="columns.length + 1" class="text-center py-12 text-gray-400">
            {{ emptyText }}
          </td>
        </tr>
        <tr
          v-else
          v-for="(row, i) in data"
          :key="row.id ?? i"
          class="border-t border-gray-100 hover:bg-gray-50 transition"
          :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'"
        >
          <td
            v-for="col in columns"
            :key="col.key"
            class="px-4 py-3 text-gray-700"
          >
            <slot :name="`cell-${col.key}`" :row="row" :value="getVal(row, col.key)">
              {{ getVal(row, col.key) ?? '—' }}
            </slot>
          </td>
          <td v-if="$slots.actions" class="px-4 py-3 text-right">
            <slot name="actions" :row="row" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
const props = defineProps({
  columns:   { type: Array,   required: true },
  data:      { type: Array,   default: () => [] },
  loading:   { type: Boolean, default: false },
  emptyText: { type: String,  default: 'Aucun résultat trouvé.' },
})

// Supporte les clés imbriquées ex: "user.name"
function getVal(row, key) {
  return key.split('.').reduce((o, k) => o?.[k], row)
}
</script>