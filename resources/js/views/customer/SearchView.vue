<script setup>
import { ref, computed, onMounted } from 'vue'
import { useSpacesStore } from '@/stores/spaces'
import SpaceCard from '@/components/SpaceCard.vue'
import FilterBar from '@/components/FilterBar.vue'
import AppNav from '@/components/AppNav.vue'

const store = useSpacesStore()

const filterBar = ref(null)
const chips     = ref([])

onMounted(() => store.fetchSpaces())

function onFilter(params) {
    store.fetchSpaces(params)
}

function onChips(list) {
    chips.value = list
}

function removeChip(key) {
    filterBar.value?.clearField(key)
}

function clearAll() {
    filterBar.value?.clearAll()
}

const total = computed(() => store.meta.total ?? store.spaces.length)
const cityChip = computed(() => chips.value.find(c => c.key === 'city')?.label)
</script>

<template>
    <AppNav />

    <div class="d-flex flex-column flex-lg-row align-items-stretch" style="max-width: 1280px; margin: 0 auto; min-height: calc(100vh - 68px);">
        <FilterBar ref="filterBar" @filter="onFilter" @chips="onChips" />

        <!-- Results column -->
        <div class="flex-grow-1 px-3 px-lg-4 py-4 py-lg-5" style="min-width: 0;">
            <h1 class="headline-md mb-3" style="color: #1A1A18;">
                <template v-if="store.loading">Searching spaces…</template>
                <template v-else>{{ total }} {{ total === 1 ? 'space' : 'spaces' }}<template v-if="cityChip"> in {{ cityChip }}</template></template>
            </h1>

            <!-- Active filter chips -->
            <div v-if="chips.length" class="d-flex flex-wrap align-items-center gap-2 mb-4">
                <span v-for="chip in chips" :key="chip.key" class="lf-active-chip">
                    {{ chip.label }}
                    <button type="button" aria-label="Remove filter" @click="removeChip(chip.key)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </span>
                <button type="button" class="btn-ghost px-2 py-1" style="font-size: 13px;" @click="clearAll">Clear all</button>
            </div>

            <!-- Loading skeleton -->
            <div v-if="store.loading" class="row g-3 g-lg-4">
                <div v-for="n in 6" :key="n" class="col-12 col-sm-6 col-xl-4">
                    <div class="lf-card overflow-hidden">
                        <div class="sk" style="aspect-ratio: 4/3; border-radius: 0;"></div>
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                <div class="sk" style="height: 15px; width: 60%;"></div>
                                <div class="sk" style="height: 15px; width: 18%; border-radius: 12px;"></div>
                            </div>
                            <div class="sk mb-3" style="height: 12px; width: 45%;"></div>
                            <div class="d-flex justify-content-between pt-3" style="border-top: 1px solid #E4DFD7;">
                                <div class="sk" style="height: 22px; width: 55px; border-radius: 6px;"></div>
                                <div class="sk" style="height: 16px; width: 72px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid -->
            <div v-else-if="store.spaces.length" class="row g-3 g-lg-4">
                <div v-for="space in store.spaces" :key="space.id" class="col-12 col-sm-6 col-xl-4">
                    <SpaceCard :space="space" />
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 360px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle mb-4" style="width: 88px; height: 88px; background: #EEEAE3;">
                    <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#707973" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                </div>
                <h2 class="headline-sm mb-2" style="color: #1A1A18;">No spaces found for these filters</h2>
                <p class="mb-4" style="font-size: 15px; color: #6B6660; max-width: 340px;">
                    Try adjusting your filters or searching in a different city.
                </p>
                <button class="btn btn-primary px-4" @click="clearAll">Clear filters</button>
            </div>

            <!-- Pagination -->
            <div v-if="store.meta.last_page > 1" class="d-flex justify-content-center mt-5">
                <nav>
                    <ul class="pagination mb-0">
                        <li
                            v-for="page in store.meta.last_page"
                            :key="page"
                            class="page-item"
                            :class="{ active: page === store.meta.current_page }"
                        >
                            <button class="page-link" @click="store.fetchSpaces({ page })">{{ page }}</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>

<style scoped>
.lf-active-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: 9999px;
    background: #FFFFFF; border: 1px solid #D4CEC6;
    font-size: 13px; color: #1A1A18;
    box-shadow: 0 1px 2px rgba(26,26,24,.05);
}
.lf-active-chip button {
    display: inline-flex; align-items: center; justify-content: center;
    background: transparent; border: none; padding: 0; color: #A09890; cursor: pointer;
    transition: color .12s ease;
}
.lf-active-chip button:hover { color: #C0392B; }
</style>
