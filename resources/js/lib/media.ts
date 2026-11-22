import type { UploadLimits } from '@/types';

/**
 * Public URL for a path stored on the `uploads` disk (e.g. a section block's
 * image). Mirrors App\Services\MediaService::url(): uploads are static files
 * under /uploads, and the URL stays relative so it works on any host.
 */
export function mediaUrl(path: string | null | undefined): string | undefined {
    if (!path) {
        return undefined;
    }

    return `/uploads/${path.replace(/^\/+/, '')}`;
}

function formatBytes(bytes: number): string {
    const mb = bytes / 1024 / 1024;

    return mb >= 1
        ? `${Number(mb.toFixed(1))} MB`
        : `${Math.ceil(bytes / 1024)} KB`;
}

/**
 * Message when the picked files exceed the server's upload limits, or null
 * when they fit. PHP drops an oversized file (or a whole oversized request)
 * before the app sees it, so without this check the upload would silently not
 * appear.
 */
export function oversizedFileMessage(
    files: FileList | File[] | null | undefined,
    limits: UploadLimits | null | undefined,
): string | null {
    if (!files || !limits) {
        return null;
    }

    const list = Array.from(files);
    const hint =
        'Pick a smaller file or raise upload_max_filesize / post_max_size in PHP.';

    if (limits.file) {
        const max = limits.file;
        const tooBig = list.find((file) => file.size > max);

        if (tooBig) {
            return `"${tooBig.name}" is ${formatBytes(tooBig.size)}, but the server accepts at most ${formatBytes(max)} per file. ${hint}`;
        }
    }

    const total = list.reduce((sum, file) => sum + file.size, 0);

    if (limits.request && total > limits.request) {
        return `These files add up to ${formatBytes(total)}, but the server accepts at most ${formatBytes(limits.request)} per save. Upload fewer at a time. ${hint}`;
    }

    return null;
}
