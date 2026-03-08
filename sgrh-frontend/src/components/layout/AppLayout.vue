<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden">

    <!-- Sidebar -->
    <AppSidebar :collapsed="sidebarCollapsed" />

    <!-- Contenu principal -->
    <div class="flex flex-col flex-1 overflow-hidden">

      <!-- Navbar top -->
      <AppNavbar @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />

      <!-- Page -->
      <main class="flex-1 overflow-y-auto p-6">
        <router-view />
      </main>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AppSidebar from './AppSidebar.vue'
import AppNavbar  from './AppNavbar.vue'

const sidebarCollapsed = ref(false)
</script>