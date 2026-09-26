<script setup lang="ts">
import { Loader2, Upload, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    modelValue: string | null;
    label: string;
    /** Upload directory; must be one the backend allowlists. */
    directory: 'steps' | 'gallery' | 'video' | 'poster' | 'avatar';
    /** Accept video files as well as images. */
    video?: boolean;
    hint?: string;
};

const props = withDefaults(defineProps<Props>(), {
    video: false,
    hint: undefined,
});

const emit = defineEmits<{ 'update:modelValue': [string | null] }>();

const uploading = ref(false);
const error = ref<string | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);

const previewUrl = computed(() =>
    props.modelValue ? `/uploads/${props.modelValue}` : null,
);

const isVideo = computed(() =>
    Boolean(props.modelValue && /\.(mp4|webm)$/i.test(props.modelValue)),
);

const accept = computed(() =>
    props.video
        ? 'image/*,video/mp4,video/webm'
        : 'image/jpeg,image/png,image/webp,image/avif,image/gif',
);

/**
 * Section media is uploaded before the section is saved, because the payload
 * stores plain paths rather than files. The endpoint hands back the stored
 * path, which is what goes into `data`.
 */
async function handleChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    uploading.value = true;
    error.value = null;

    const body = new FormData();
    body.append('file', file);
    body.append('directory', props.directory);

    try {
        const response = await fetch('/admin/landing-media', {
            method: 'POST',
            body,
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(
                    readCookie('XSRF-TOKEN') ?? '',
                ),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Upload failed with status ${response.status}`);
        }

        const payload = (await response.json()) as { path: string };

        emit('update:modelValue', payload.path);
    } catch {
        error.value =
            'Upload failed. Check the file type and size, then try again.';
    } finally {
        uploading.value = false;

        // Allow re-picking the same file after a failure.
        if (inputRef.value) {
            inputRef.value.value = '';
        }
    }
}

function readCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(^| )${name}=([^;]+)`));

    return match ? match[2] : null;
}

function clear() {
    emit('update:modelValue', null);
}
</script>

<template>
    <div class="grid gap-2">
        <Label>{{ props.label }}</Label>

        <div v-if="previewUrl" class="flex items-start gap-3">
            <video
                v-if="isVideo"
                :src="previewUrl"
                class="h-20 w-32 rounded-md border border-border object-cover"
                muted
            />
            <img
                v-else
                :src="previewUrl"
                alt=""
                class="h-20 w-32 rounded-md border border-border object-cover"
            />
            <Button type="button" variant="ghost" size="sm" @click="clear">
                <X class="size-4" />
                Remove
            </Button>
        </div>

        <div class="flex items-center gap-2">
            <Input
                ref="inputRef"
                type="file"
                :accept="accept"
                :disabled="uploading"
                class="max-w-sm"
                @change="handleChange"
            />
            <Loader2
                v-if="uploading"
                class="size-4 animate-spin text-muted-foreground"
            />
            <Upload v-else class="size-4 text-muted-foreground" />
        </div>

        <p v-if="props.hint" class="text-xs text-muted-foreground">
            {{ props.hint }}
        </p>
        <p v-if="error" class="text-xs text-destructive">{{ error }}</p>
    </div>
</template>
