<template>
  <div :class="['flex items-baseline gap-xs', sizeClasses]">
    <span class="text-text-secondary">{{ currency }}</span>
    <span class="font-mono font-500" :class="textSizeClass">{{ formattedPrice }}</span>
    <span v-if="period" class="text-text-tertiary text-body-sm">/{{ period }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  price: {
    type: Number,
    required: true,
  },
  currency: {
    type: String,
    default: '$',
  },
  period: {
    type: String,
    default: '', // e.g., 'month', 'night', 'hour'
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
  },
})

const formattedPrice = computed(() => {
  return props.price.toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  })
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'text-body-sm',
    md: 'text-body-md',
    lg: 'text-heading-sm',
    xl: 'text-heading-md',
  }
  return sizes[props.size]
})

const textSizeClass = computed(() => {
  const sizes = {
    sm: 'text-body-sm',
    md: 'text-body-md',
    lg: 'text-heading-sm',
    xl: 'text-heading-md',
  }
  return sizes[props.size]
})
</script>
