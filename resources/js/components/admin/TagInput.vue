<script setup lang="ts">
import { X } from '@lucide/vue';
import { ref } from 'vue';
import { Input } from '@/components/ui/input';

type Props = {
    /**
     * Form field name; values are submitted as `name[]`. Optional, because
     * callers that read the list through `v-model` submit it themselves.
     */
    name?: string;
    modelValue?: string[] | null;
    placeholder?: string;
};

const props = defineProps<Props>();

/** Emitted alongside the hidden inputs, so the component works either way. */
const emit = defineEmits<{ 'update:modelValue': [string[]] }>();

const tags = ref<string[]>([...(props.modelValue ?? [])]);
const draft = ref('');

function sync() {
    emit('update:modelValue', [...tags.value]);
}

function addTag() {
    const value = draft.value.trim();

    if (value === '' || tags.value.includes(value)) {
        draft.value = '';

        return;
    }

    tags.value.push(value);
    draft.value = '';
    sync();
}

function removeTag(index: number) {
    tags.value.splice(index, 1);
    sync();
}

/** Backspace on an empty input removes the last tag, as in most tag fields. */
function handleBackspace() {
    if (draft.value === '' && tags.value.length > 0) {
        tags.value.pop();
        sync();
    }
}
</script>

<template>
    <div class="flex flex-col gap-2">
        <div v-if="tags.length" class="flex flex-wrap gap-1.5">
            <span
                v-for="(tag, index) in tags"
                :key="`${tag}-${index}`"
                class="inline-flex items-center gap-1 rounded-md bg-secondary px-2 py-1 text-xs font-medium text-secondary-foreground"
            >
                {{ tag }}
                <button
                    type="button"
                    class="hover:text-destructive"
                    :aria-label="`Remove ${tag}`"
                    @click="removeTag(index)"
                >
                    <X class="size-3" />
                </button>
            </span>
        </div>

        <Input
            v-model="draft"
            :placeholder="placeholder ?? 'Type and press Enter'"
            @keydown.enter.prevent="addTag"
            @keydown.delete="handleBackspace"
            @blur="addTag"
        />

        <!-- The actual submitted values, for native form usage. -->
        <template v-if="name">
            <input
                v-for="(tag, index) in tags"
                :key="`field-${index}`"
                type="hidden"
                :name="`${name}[]`"
                :value="tag"
            />
        </template>
    </div>
</template>
