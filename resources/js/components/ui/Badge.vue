<template>
  <span :class="['badge-base', badgeClasses]">
    <slot />
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['success', 'warning', 'error', 'info', 'default'].includes(value),
  },
})

const badgeClasses = computed(() => {
  const variants = {
    success: 'badge-success',
    warning: 'badge-warning',
    error: 'badge-error',
    info: 'badge-info',
    default: 'badge-default',
  }
  return variants[props.variant]
})
</script>
