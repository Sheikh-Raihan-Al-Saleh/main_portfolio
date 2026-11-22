<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Upload, X } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { oversizedFileMessage } from '@/lib/media';

type Props = {
    name: string;
    label: string;
    /** Name of the companion boolean field that clears the stored file. */
    removeName?: string;
    currentUrl?: string | null;
    accept?: string;
    hint?: string;
    /** Render the existing file as an image preview rather than a filename. */
    preview?: boolean;
};

const props = defineProps<Props>();

const page = usePage();
const previewUrl = ref<string | null>(props.currentUrl ?? null);
const removed = ref(false);
const sizeError = ref<string | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);

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
        previewUrl.value = removed.value ? null : (props.currentUrl ?? null);

        return;
    }

    if (!file) {
        return;
    }

    removed.value = false;
    previewUrl.value = props.preview ? URL.createObjectURL(file) : file.name;
}

/** Clears both the newly picked file and the previously stored one. */
function clear() {
    removed.value = true;
    previewUrl.value = null;

    if (inputRef.value) {
        inputRef.value.value = '';
    }
}
</script>

<template>
    <div class="grid gap-2">
        <Label :for="name">{{ label }}</Label>

        <div v-if="previewUrl" class="flex items-center gap-3">
            <img
                v-if="preview"
                :src="previewUrl"
                alt=""
                class="size-16 rounded-md border border-border bg-muted object-cover"
            />
            <span v-else class="truncate text-sm text-muted-foreground">
                {{ previewUrl }}
            </span>

            <Button
                v-if="removeName"
                type="button"
                variant="ghost"
                size="sm"
                @click="clear"
            >
                <X class="size-4" />
                Remove
            </Button>
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
