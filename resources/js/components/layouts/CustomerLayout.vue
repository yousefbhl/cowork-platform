<template>
  <div class="min-h-screen bg-bg-primary flex flex-col">
    <AppNav />
    <main class="flex-1">
      <slot />
    </main>
    <footer class="bg-bg-secondary border-t border-border-light py-3xl px-lg">
      <div class="page-container">
        <div class="grid grid-cols-4 gap-2xl mb-2xl">
          <div>
            <h3 class="text-heading-sm mb-lg">Company</h3>
            <ul class="space-y-sm text-body-sm text-text-secondary">
              <li><a href="#" class="hover:text-text-primary transition-colors">About Us</a></li>
              <li><a href="#" class="hover:text-text-primary transition-colors">Blog</a></li>
              <li><a href="#" class="hover:text-text-primary transition-colors">Careers</a></li>
            </ul>
          </div>
          <div>
            <h3 class="text-heading-sm mb-lg">Support</h3>
            <ul class="space-y-sm text-body-sm text-text-secondary">
              <li><a href="#" class="hover:text-text-primary transition-colors">Help Center</a></li>
              <li><a href="#" class="hover:text-text-primary transition-colors">Contact Us</a></li>
              <li><a href="#" class="hover:text-text-primary transition-colors">FAQs</a></li>
            </ul>
          </div>
          <div>
            <h3 class="text-heading-sm mb-lg">Legal</h3>
            <ul class="space-y-sm text-body-sm text-text-secondary">
              <li><a href="#" class="hover:text-text-primary transition-colors">Privacy Policy</a></li>
              <li><a href="#" class="hover:text-text-primary transition-colors">Terms of Service</a></li>
              <li><a href="#" class="hover:text-text-primary transition-colors">Cookies</a></li>
            </ul>
          </div>
          <div>
            <h3 class="text-heading-sm mb-lg">Social</h3>
            <ul class="space-y-sm text-body-sm text-text-secondary">
              <li><a href="#" class="hover:text-text-primary transition-colors">Twitter</a></li>
              <li><a href="#" class="hover:text-text-primary transition-colors">LinkedIn</a></li>
              <li><a href="#" class="hover:text-text-primary transition-colors">Instagram</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-border-light pt-xl flex-between text-body-sm text-text-secondary">
          <p>&copy; 2024 CoworkPlatform. All rights reserved.</p>
          <p>Made with care for remote workers</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import AppNav from '../AppNav.vue'
</script>
