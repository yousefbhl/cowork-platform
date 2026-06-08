import { ref, watch } from 'vue'

export function useDebouncedSearch(callback, delay = 400) {
    const query = ref('')
    let timeout = null

    watch(query, (val) => {
        clearTimeout(timeout)
        timeout = setTimeout(() => callback(val), delay)
    })

    return { query }
}
