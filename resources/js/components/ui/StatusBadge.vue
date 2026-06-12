<template>
  <span :class="['inline-flex items-center gap-xs px-md py-xs rounded-md text-label-md font-600', variantClasses]">
    <span :class="['w-2 h-2 rounded-full', dotColor]"></span>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => ['available', 'booked', 'pending', 'cancelled', 'completed', 'disputed'].includes(value),
  },
})

const statusConfig = {
  available: {
    label: 'Available',
    bgColor: 'bg-accent-green bg-opacity-20',
    textColor: 'text-accent-green',
    dotColor: 'bg-accent-green',
  },
  booked: {
    label: 'Booked',
    bgColor: 'bg-accent-blue bg-opacity-20',
    textColor: 'text-accent-blue',
    dotColor: 'bg-accent-blue',
  },
  pending: {
    label: 'Pending',
    bgColor: 'bg-accent-amber bg-opacity-20',
    textColor: 'text-accent-amber',
    dotColor: 'bg-accent-amber',
  },
  cancelled: {
    label: 'Cancelled',
    bgColor: 'bg-accent-red bg-opacity-20',
    textColor: 'text-accent-red',
    dotColor: 'bg-accent-red',
  },
  completed: {
    label: 'Completed',
    bgColor: 'bg-accent-green bg-opacity-20',
    textColor: 'text-accent-green',
    dotColor: 'bg-accent-green',
  },
  disputed: {
    label: 'Disputed',
    bgColor: 'bg-accent-red bg-opacity-20',
    textColor: 'text-accent-red',
    dotColor: 'bg-accent-red',
  },
}

const config = computed(() => statusConfig[props.status] || statusConfig.pending)
const label = computed(() => config.value.label)
const variantClasses = computed(() => `${config.value.bgColor} ${config.value.textColor}`)
const dotColor = computed(() => config.value.dotColor)
</script>
