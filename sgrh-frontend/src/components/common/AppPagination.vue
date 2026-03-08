<template>
  <div class="flex items-center justify-between mt-4 text-sm text-gray-600">
    <p>
      Page <span class="font-medium">{{ meta.current_page }}</span>
      sur <span class="font-medium">{{ meta.last_page }}</span>
      · <span class="font-medium">{{ meta.total }}</span> résultat(s)
    </p>
    <div class="flex gap-2">
      <button
        @click="$emit('change', meta.current_page - 1)"
        :disabled="meta.current_page === 1"
        class="btn-secondary px-3 py-1.5 text-xs disabled:opacity-40"
      >
        ← Précédent
      </button>
      <button
        @click="$emit('change', meta.current_page + 1)"
        :disabled="meta.current_page === meta.last_page"
        class="btn-secondary px-3 py-1.5 text-xs disabled:opacity-40"
      >
        Suivant →
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({ meta: { type: Object, required: true } })
defineEmits(['change'])
</script>