<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Upload, X } from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
import MediaPreview from '@/components/MediaPreview.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { isVideoUrl, oversizedFileMessage } from '@/composables/media';
import type { FileFieldProps } from '@/types';

const props = defineProps<FileFieldProps>();

const page = usePage();
const previewUrl = ref<string | null>(props.currentUrl ?? null);
const previewIsVideo = ref(isVideoUrl(props.currentUrl));
const removed = ref(false);
const sizeError = ref<string | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);

/** The blob URL of the picked file, freed when it is replaced or unmounted. */
let objectUrl: string | null = null;

function releaseObjectUrl(): void {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }
}

/** Show the stored file again (or nothing, once it is marked for removal). */
function showStoredFile(): void {
    releaseObjectUrl();
    previewUrl.value = removed.value ? null : (props.currentUrl ?? null);
    previewIsVideo.value = isVideoUrl(previewUrl.value);
}

function handleChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    sizeError.value = oversizedFileMessage(
        input.files,
        page.props.uploadLimits,
    );

    // Drop a file the server would reject, so the form never submits it.
    if (sizeError.value) {
        input.value = '';
        showStoredFile();

        return;
    }

    if (!file) {
        showStoredFile();

        return;
    }

    removed.value = false;
    releaseObjectUrl();

    if (props.preview) {
        objectUrl = URL.createObjectURL(file);
        previewUrl.value = objectUrl;
        previewIsVideo.value = file.type.startsWith('video/');
    } else {
        previewUrl.value = file.name;
        previewIsVideo.value = false;
    }
}

/** Clears both the newly picked file and the previously stored one. */
function clear() {
    removed.value = true;
    releaseObjectUrl();
    previewUrl.value = null;

    if (inputRef.value) {
        inputRef.value.value = '';
    }
}

onBeforeUnmount(releaseObjectUrl);
</script>

<template>
    <div class="grid min-w-0 gap-2">
        <Label :for="name">{{ label }}</Label>

        <div v-if="previewUrl" class="grid gap-2">
            <MediaPreview
                v-if="preview"
                :src="previewUrl"
                :video="previewIsVideo"
                :alt="`${label} preview`"
            />
            <span v-else class="truncate text-sm text-muted-foreground">
                {{ previewUrl }}
            </span>

            <div v-if="removeName">
                <Button type="button" variant="ghost" size="sm" @click="clear">
                    <X class="size-4" />
                    Remove
                </Button>
            </div>
        </div>

        <Input
            :id="name"
            ref="inputRef"
            type="file"
            :name="name"
            :accept="accept"
            class="file:mr-3 file:text-sm file:text-foreground"
            @change="handleChange"
        />

        <p v-if="hint" class="text-xs text-muted-foreground">
            <Upload class="mr-1 inline size-3" />
            {{ hint }}
        </p>
        <p v-if="sizeError" class="text-xs text-destructive">
            {{ sizeError }}
        </p>

        <input
            v-if="removeName"
            type="hidden"
            :name="removeName"
            :value="removed ? 1 : 0"
        />
    </div>
</template>
