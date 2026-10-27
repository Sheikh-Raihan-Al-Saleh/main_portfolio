/**
 * Public URL for a path stored on the `uploads` disk. Uploads are static files
 * under public/uploads, so the URL is built here rather than per component.
 */
export function uploadUrl(path: string | null | undefined): string | undefined {
    return path ? `/uploads/${path}` : undefined;
}
