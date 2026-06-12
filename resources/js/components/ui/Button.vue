<template>
  <button
    :class="[
      'btn-base',
      variantClasses,
      sizeClasses,
      {
        'opacity-50 cursor-not-allowed': disabled,
        'w-full': fullWidth,
      }
    ]"
    :disabled="disabled"
    @click="$emit('click')"
  >
    <span v-if="loading" class="inline-block mr-sm animate-spin">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" opacity="0.25"></circle>
        <path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" fill="currentColor"></path>
      </svg>
    </span>
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'ghost'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  fullWidth: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['click'])

const variantClasses = computed(() => {
  const variants = {
    primary: 'btn-primary',
    secondary: 'btn-secondary',
    ghost: 'btn-ghost',
  }
  return variants[props.variant]
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-md py-xs text-label-md',
    md: 'px-lg py-md text-body-sm',
    lg: 'px-xl py-lg text-body-md',
  }
  return sizes[props.size]
})
</script>
