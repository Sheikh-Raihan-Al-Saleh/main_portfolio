<script setup lang="ts">
/**
 * Page-wide film grain. Rendered once at the root of the public layout and
 * fixed to the viewport, so it stays still while content scrolls beneath it —
 * that stillness is what makes it read as a lens artefact rather than texture
 * painted onto the page.
 */
</script>

<template>
    <div
        class="bg-grain pointer-events-none fixed inset-0 z-[9999]"
        aria-hidden="true"
    />
</template>
