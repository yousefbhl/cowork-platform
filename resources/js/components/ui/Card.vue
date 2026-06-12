<template>
  <div :class="['card-base', { 'card-hover': hoverable, 'shadow-md': elevated }]">
    <slot />
  </div>
</template>

<script setup>
defineProps({
  hoverable: {
    type: Boolean,
    default: false,
  },
  elevated: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
/* Ensure proper padding for content */
</style>
