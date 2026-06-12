<template>
  <aside class="w-64 bg-bg-secondary border-r border-border-light min-h-screen fixed left-0 top-0 overflow-y-auto">
    <div class="p-xl border-b border-border-light">
      <h1 class="text-heading-md font-600">CoworkPlatform</h1>
    </div>
    
    <nav class="p-lg">
      <ul class="space-y-md">
        <li v-for="item in items" :key="item.id">
          <router-link
            :to="item.route"
            :class="[
              'flex items-center gap-md px-lg py-md rounded-md transition-colors',
              isActive(item.route)
                ? 'bg-accent-violet bg-opacity-20 text-accent-violet'
                : 'text-text-secondary hover:text-text-primary hover:bg-bg-tertiary'
            ]"
          >
            <svg v-if="item.icon" :class="'w-5 h-5'">
              <use :xlink:href="`#icon-${item.icon}`" />
            </svg>
            <span class="text-body-sm font-500">{{ item.label }}</span>
          </router-link>
        </li>
      </ul>
    </nav>

    <div class="p-lg border-t border-border-light mt-auto">
      <button class="btn-secondary w-full flex items-center justify-center gap-md">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        Logout
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import { computed } from 'vue'

const router = useRouter()
const route = useRoute()

const props = defineProps({
  items: {
    type: Array,
    required: true,
    validator: (value) => value.every(item => item.id && item.label && item.route),
  },
})

const isActive = (routePath) => {
  return route.path === routePath || route.path.startsWith(routePath)
}
</script>
