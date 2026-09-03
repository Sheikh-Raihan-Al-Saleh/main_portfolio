<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import { CircleCheckIcon, InfoIcon, Loader2Icon, OctagonXIcon, TriangleAlertIcon, XIcon } from "@lucide/vue"
import { Toaster as Sonner } from "vue-sonner"
import { cn } from "@/lib/utils"

import 'vue-sonner/style.css';

const props = defineProps<ToasterProps>()
</script>

<template>
  <Sonner
    :class="cn('toaster group', props.class)"
    :style="{
      '--normal-bg': 'var(--popover)',
      '--normal-text': 'var(--popover-foreground)',
      '--normal-border': 'var(--border)',
      '--success-bg': 'hsl(142 71% 95%)',
      '--success-text': 'hsl(142 71% 25%)',
      '--success-border': 'hsl(142 71% 80%)',
      '--error-bg': 'hsl(0 84% 95%)',
      '--error-text': 'hsl(0 84% 30%)',
      '--error-border': 'hsl(0 84% 80%)',
      '--warning-bg': 'hsl(38 92% 95%)',
      '--warning-text': 'hsl(38 92% 25%)',
      '--warning-border': 'hsl(38 92% 80%)',
      '--info-bg': 'hsl(217 91% 95%)',
      '--info-text': 'hsl(217 91% 30%)',
      '--info-border': 'hsl(217 91% 80%)',
      '--border-radius': 'var(--radius)',
    }"
    v-bind="props"
  >
    <template #success-icon>
      <CircleCheckIcon class="size-4 text-emerald-500" />
    </template>
    <template #info-icon>
      <InfoIcon class="size-4 text-blue-500" />
    </template>
    <template #warning-icon>
      <TriangleAlertIcon class="size-4 text-amber-500" />
    </template>
    <template #error-icon>
      <OctagonXIcon class="size-4 text-red-500" />
    </template>
    <template #loading-icon>
      <div>
        <Loader2Icon class="size-4 animate-spin text-muted-foreground" />
      </div>
    </template>
    <template #close-icon>
      <XIcon class="size-4" />
    </template>
  </Sonner>
</template>
