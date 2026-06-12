<template>
  <Transition name="modal">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 animate-fade-in" @click.self="closeModal">
      <div class="card-base w-full max-w-md mx-lg animate-slide-in-up">
        <div class="flex-between px-xl py-lg border-b border-border-light">
          <h2 class="text-heading-sm">{{ title }}</h2>
          <button
            class="text-text-secondary hover:text-text-primary transition-colors"
            @click="closeModal"
          >
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="px-xl py-lg">
          <slot />
        </div>
        <div v-if="$slots.footer" class="flex gap-md px-xl py-lg border-t border-border-light">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Modal',
  },
})

const emit = defineEmits(['update:modelValue'])

const closeModal = () => {
  emit('update:modelValue', false)
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
