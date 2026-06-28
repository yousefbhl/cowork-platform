/**
 * Returns the correct src URL for a photo path that may be either a local
 * storage-relative path ("spaces/abc.jpg") or a full external URL ("https://...").
 */
export function photoUrl(path) {
    if (!path) return null
    return path.startsWith('http') ? path : `/storage/${path}`
}
