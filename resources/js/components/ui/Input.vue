<template>
  <div class="flex flex-col gap-md">
    <label v-if="label" :for="id" class="text-label-md text-text-primary font-600">
      {{ label }}
      <span v-if="required" class="text-accent-red">*</span>
    </label>
    <input
      :id="id"
      :type="type"
      :placeholder="placeholder"
      :value="modelValue"
      class="input-base"
      :disabled="disabled"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <span v-if="error" class="text-label-md text-accent-red">{{ error }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['update:modelValue'])

const id = computed(() => Math.random().toString(36).substring(2, 9))
</script>
