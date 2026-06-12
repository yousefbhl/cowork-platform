<template>
  <div class="min-h-screen bg-bg-primary flex">
    <Sidebar :items="sidebarItems" />
    
    <div class="flex-1 ml-64 flex flex-col">
      <!-- Dashboard Header -->
      <div class="bg-bg-secondary border-b border-border-light px-xl py-lg sticky top-0 z-10">
        <div class="flex-between">
          <div>
            <h1 class="text-heading-lg font-600">{{ title }}</h1>
            <p v-if="subtitle" class="text-body-sm text-text-secondary mt-xs">{{ subtitle }}</p>
          </div>
          <div class="flex items-center gap-lg">
            <button class="p-md hover:bg-bg-tertiary rounded-md transition-colors">
              <svg class="w-6 h-6 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </button>
            <img src="https://via.placeholder.com/40" alt="Profile" class="w-10 h-10 rounded-full">
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <main class="flex-1 overflow-auto">
        <div class="p-xl">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  sidebarItems: {
    type: Array,
    required: true,
  },
})

import Sidebar from '../ui/Sidebar.vue'
</script>
