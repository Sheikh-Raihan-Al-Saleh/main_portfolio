<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';

type Props = {
    images: string[];
};

const props = defineProps<Props>();

const laptopSrc = computed(() => props.images[0] ?? null);
const tabletSrc = computed(() => props.images[1] ?? props.images[0] ?? null);
const phoneSrc = computed(() => props.images[2] ?? props.images[0] ?? null);
</script>

<template>
    <div class="landing-stage relative w-full py-10 sm:py-14">
        <!-- soft background glow -->
        <div
            class="hero-glow pointer-events-none absolute inset-0"
            aria-hidden="true"
        />
        <div
            class="hero-glow-2 pointer-events-none absolute inset-0"
            aria-hidden="true"
        />
        <div
            class="hero-glow-3 pointer-events-none absolute inset-0"
            aria-hidden="true"
        />

        <!-- ── 3D DEVICE STAGE ── -->
        <div
            class="relative mx-auto flex max-w-[1100px] items-end justify-center gap-0 px-2 sm:px-6"
        >
            <!-- TABLET — front left -->
            <motion.div
                class="device-shell animate-float-tablet relative z-20 hidden w-[34%] max-w-[340px] shrink-0 -translate-y-2 sm:block lg:w-[30%]"
                :initial="{ opacity: 0, y: 40, rotateY: 12, rotateX: 4 }"
                :animate="{ opacity: 1, y: 0, rotateY: -6, rotateX: 2 }"
                :transition="{
                    duration: 1.1,
                    ease: [0.16, 1, 0.3, 1],
                    delay: 0.15,
                }"
                :while-hover="{ rotateY: -2, rotateX: 1, y: -6 }"
            >
                <div class="device-tablet-frame">
                    <div
                        class="device-screen overflow-hidden rounded-[10px] bg-white"
                    >
                        <img
                            v-if="tabletSrc"
                            :src="tabletSrc"
                            alt="Tablet preview"
                            class="h-[360px] w-full object-cover object-top"
                            loading="eager"
                        />
                        <!-- Tablet dashboard mock (fallback) -->
                        <div
                            v-else
                            class="flex min-h-[360px] flex-col bg-white"
                        >
                            <!-- top bar -->
                            <div
                                class="flex items-center gap-2 border-b border-slate-100 px-3 py-2.5"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="size-2 rounded-full bg-red-400"
                                    />
                                    <span
                                        class="size-2 rounded-full bg-yellow-400"
                                    />
                                    <span
                                        class="size-2 rounded-full bg-green-400"
                                    />
                                </div>
                                <span
                                    class="ml-2 text-[9px] font-bold tracking-widest text-[#2563eb]"
                                    >OUTLET EXPRESS</span
                                >
                            </div>
                            <div class="flex flex-1">
                                <div
                                    class="dash-sidebar hidden w-[28%] p-2 sm:flex sm:flex-col sm:gap-1"
                                >
                                    <div
                                        class="mb-2 flex items-center gap-1.5 px-1 py-1"
                                    >
                                        <span
                                            class="grid size-5 place-items-center rounded bg-[#2563eb] text-[9px] font-bold text-white"
                                            >α</span
                                        >
                                        <span
                                            class="text-[8px] leading-none font-bold text-slate-700"
                                            >OUTLET<br />EXPRESS</span
                                        >
                                    </div>
                                    <span
                                        class="rounded bg-[#2563eb] px-2 py-1.5 text-[8px] font-semibold text-white"
                                        >Dashboard</span
                                    >
                                    <span
                                        class="px-2 py-1 text-[8px] text-slate-500"
                                        >Products</span
                                    >
                                    <span
                                        class="px-2 py-1 text-[8px] text-slate-500"
                                        >Billing</span
                                    >
                                </div>
                                <div class="flex-1 p-2">
                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <span
                                            class="text-[8px] font-bold text-slate-800"
                                            >Dashboard</span
                                        >
                                        <span
                                            class="rounded bg-slate-900 px-1.5 py-0.5 text-[7px] text-white"
                                            >Daily</span
                                        >
                                    </div>
                                    <div class="grid grid-cols-2 gap-1">
                                        <div
                                            class="dash-card-orange rounded p-1.5 text-white"
                                        >
                                            <div class="text-[6px] opacity-80">
                                                Total Sales
                                            </div>
                                            <div class="text-[10px] font-bold">
                                                ৳ 48,592
                                            </div>
                                            <svg
                                                viewBox="0 0 60 14"
                                                class="mt-1 h-3 w-full"
                                            >
                                                <path
                                                    d="M0 10 L12 6 L24 9 L36 4 L48 7 L60 3"
                                                    fill="none"
                                                    stroke="white"
                                                    stroke-width="1.2"
                                                    opacity="0.85"
                                                />
                                            </svg>
                                        </div>
                                        <div
                                            class="dash-card-purple rounded p-1.5 text-white"
                                        >
                                            <div class="text-[6px] opacity-80">
                                                Orders
                                            </div>
                                            <div class="text-[10px] font-bold">
                                                1,284
                                            </div>
                                            <svg
                                                viewBox="0 0 60 14"
                                                class="mt-1 h-3 w-full"
                                            >
                                                <path
                                                    d="M0 8 L15 10 L30 5 L45 9 L60 4"
                                                    fill="none"
                                                    stroke="white"
                                                    stroke-width="1.2"
                                                    opacity="0.85"
                                                />
                                            </svg>
                                        </div>
                                        <div
                                            class="dash-card-green rounded p-1.5 text-white"
                                        >
                                            <div class="text-[6px] opacity-80">
                                                Customers
                                            </div>
                                            <div class="text-[10px] font-bold">
                                                892
                                            </div>
                                            <svg
                                                viewBox="0 0 60 14"
                                                class="mt-1 h-3 w-full"
                                            >
                                                <path
                                                    d="M0 6 L15 3 L30 7 L45 4 L60 8"
                                                    fill="none"
                                                    stroke="white"
                                                    stroke-width="1.2"
                                                    opacity="0.85"
                                                />
                                            </svg>
                                        </div>
                                        <div
                                            class="dash-card-blue rounded p-1.5 text-white"
                                        >
                                            <div class="text-[6px] opacity-80">
                                                Revenue
                                            </div>
                                            <div class="text-[10px] font-bold">
                                                ৳ 2.4M
                                            </div>
                                            <svg
                                                viewBox="0 0 60 14"
                                                class="mt-1 h-3 w-full"
                                            >
                                                <path
                                                    d="M0 9 L15 6 L30 10 L45 5 L60 7"
                                                    fill="none"
                                                    stroke="white"
                                                    stroke-width="1.2"
                                                    opacity="0.85"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="mt-2 grid grid-cols-2 gap-1">
                                        <div
                                            class="dash-chart-bar rounded p-1.5"
                                        >
                                            <div
                                                class="mb-1 flex h-10 items-end gap-0.5"
                                            >
                                                <span
                                                    v-for="h in [
                                                        30, 55, 40, 70, 45, 60,
                                                        35,
                                                    ]"
                                                    :key="h"
                                                    class="flex-1 rounded-sm bg-white/90"
                                                    :style="{ height: h + '%' }"
                                                />
                                            </div>
                                            <div
                                                class="text-[6px] text-white/60"
                                            >
                                                Daily Sales
                                            </div>
                                        </div>
                                        <div
                                            class="rounded border border-slate-100 p-1.5"
                                        >
                                            <div
                                                class="text-[6px] font-semibold text-slate-700"
                                            >
                                                Revenue
                                            </div>
                                            <svg
                                                viewBox="0 0 60 24"
                                                class="mt-1 h-8 w-full"
                                            >
                                                <path
                                                    d="M0 18 L10 14 L20 16 L30 8 L40 12 L50 6 L60 10"
                                                    fill="none"
                                                    stroke="#2563eb"
                                                    stroke-width="1.4"
                                                />
                                                <path
                                                    d="M0 18 L10 14 L20 16 L30 8 L40 12 L50 6 L60 10 L60 24 L0 24 Z"
                                                    fill="#2563eb"
                                                    opacity="0.12"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- shadow -->
                <div
                    class="mx-auto mt-3 h-3 w-[80%] rounded-full bg-slate-900/10 blur-xl"
                />
            </motion.div>

            <!-- LAPTOP — center back, dominant -->
            <motion.div
                class="device-shell animate-float-laptop relative z-10 w-full max-w-[640px] shrink sm:w-[72%] lg:w-[62%]"
                :initial="{ opacity: 0, y: 50, scale: 0.97 }"
                :animate="{ opacity: 1, y: 0, scale: 1 }"
                :transition="{
                    duration: 1,
                    ease: [0.16, 1, 0.3, 1],
                    delay: 0.05,
                }"
                :while-hover="{ y: -8, rotateX: 1 }"
            >
                <div class="device-laptop-frame">
                    <div
                        class="device-screen aspect-[16/10] overflow-hidden bg-white"
                    >
                        <img
                            v-if="laptopSrc"
                            :src="laptopSrc"
                            alt="Desktop preview"
                            class="h-full w-full object-cover object-top"
                            loading="eager"
                        />
                        <!-- Laptop dashboard — faithful to reference (fallback) -->
                        <div v-else class="flex h-full">
                            <!-- Sidebar -->
                            <div
                                class="dash-sidebar flex w-[19%] flex-col gap-1 p-2.5"
                            >
                                <div class="mb-3 flex items-center gap-2">
                                    <span
                                        class="grid size-7 place-items-center rounded-lg bg-[#2563eb] text-[11px] font-bold text-white"
                                        >α</span
                                    >
                                    <span
                                        class="text-[9px] leading-none font-extrabold tracking-tight text-[#2563eb]"
                                        >OUTLET<br />EXPRESS</span
                                    >
                                </div>
                                <div
                                    class="rounded-lg bg-[#2563eb] px-2.5 py-2 text-[10px] font-semibold text-white shadow-sm"
                                >
                                    ◎ Dashboard
                                </div>
                                <div
                                    class="px-2.5 py-1.5 text-[10px] text-slate-500"
                                >
                                    Products
                                </div>
                                <div
                                    class="px-2.5 py-1.5 text-[10px] text-slate-500"
                                >
                                    Billing
                                </div>
                                <div
                                    class="px-2.5 py-1.5 text-[10px] text-slate-500"
                                >
                                    Customers
                                </div>
                                <div
                                    class="px-2.5 py-1.5 text-[10px] text-slate-500"
                                >
                                    Reports
                                </div>
                                <div
                                    class="mt-auto rounded-lg bg-slate-900 px-2 py-2 text-[9px] text-white"
                                >
                                    <div class="font-semibold">Upgrade Pro</div>
                                    <div class="text-[8px] opacity-70">
                                        Get more features
                                    </div>
                                </div>
                            </div>
                            <!-- Main -->
                            <div class="flex flex-1 flex-col bg-[#fcfdff] p-3">
                                <div
                                    class="mb-2.5 flex items-center justify-between"
                                >
                                    <div>
                                        <div
                                            class="text-[11px] font-bold text-slate-800"
                                        >
                                            Dashboard
                                        </div>
                                        <div class="text-[8px] text-slate-400">
                                            Welcome back, Monica
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="rounded-full bg-slate-900 px-2 py-1 text-[8px] font-medium text-white"
                                            >Daily</span
                                        >
                                        <span
                                            class="px-2 py-1 text-[8px] text-slate-400"
                                            >Weekly</span
                                        >
                                        <span
                                            class="px-2 py-1 text-[8px] text-slate-400"
                                            >Monthly</span
                                        >
                                    </div>
                                </div>
                                <!-- 4 stat cards -->
                                <div class="grid grid-cols-4 gap-2">
                                    <div
                                        class="dash-card-orange rounded-xl p-2.5 text-white shadow-sm"
                                    >
                                        <div
                                            class="text-[8px] font-medium opacity-90"
                                        >
                                            Total Revenue
                                        </div>
                                        <div
                                            class="mt-0.5 text-[13px] font-bold"
                                        >
                                            ৳ 1,48,592
                                        </div>
                                        <svg
                                            viewBox="0 0 80 18"
                                            class="mt-1.5 h-4 w-full"
                                        >
                                            <path
                                                d="M0 12 L14 8 L28 11 L42 5 L56 8 L80 3"
                                                fill="none"
                                                stroke="white"
                                                stroke-width="1.6"
                                                stroke-linecap="round"
                                                opacity="0.9"
                                            />
                                        </svg>
                                    </div>
                                    <div
                                        class="dash-card-purple rounded-xl p-2.5 text-white shadow-sm"
                                    >
                                        <div
                                            class="text-[8px] font-medium opacity-90"
                                        >
                                            Total Orders
                                        </div>
                                        <div
                                            class="mt-0.5 text-[13px] font-bold"
                                        >
                                            2,543
                                        </div>
                                        <svg
                                            viewBox="0 0 80 18"
                                            class="mt-1.5 h-4 w-full"
                                        >
                                            <path
                                                d="M0 10 L14 12 L28 7 L42 11 L56 6 L80 9"
                                                fill="none"
                                                stroke="white"
                                                stroke-width="1.6"
                                                stroke-linecap="round"
                                                opacity="0.9"
                                            />
                                        </svg>
                                    </div>
                                    <div
                                        class="dash-card-green rounded-xl p-2.5 text-white shadow-sm"
                                    >
                                        <div
                                            class="text-[8px] font-medium opacity-90"
                                        >
                                            Customers
                                        </div>
                                        <div
                                            class="mt-0.5 text-[13px] font-bold"
                                        >
                                            1,892
                                        </div>
                                        <svg
                                            viewBox="0 0 80 18"
                                            class="mt-1.5 h-4 w-full"
                                        >
                                            <path
                                                d="M0 8 L14 4 L28 9 L42 5 L56 9 L80 6"
                                                fill="none"
                                                stroke="white"
                                                stroke-width="1.6"
                                                stroke-linecap="round"
                                                opacity="0.9"
                                            />
                                        </svg>
                                    </div>
                                    <div
                                        class="dash-card-blue rounded-xl p-2.5 text-white shadow-sm"
                                    >
                                        <div
                                            class="text-[8px] font-medium opacity-90"
                                        >
                                            Products
                                        </div>
                                        <div
                                            class="mt-0.5 text-[13px] font-bold"
                                        >
                                            3,268
                                        </div>
                                        <svg
                                            viewBox="0 0 80 18"
                                            class="mt-1.5 h-4 w-full"
                                        >
                                            <path
                                                d="M0 11 L14 9 L28 12 L42 7 L56 10 L80 5"
                                                fill="none"
                                                stroke="white"
                                                stroke-width="1.6"
                                                stroke-linecap="round"
                                                opacity="0.9"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Charts row -->
                                <div
                                    class="mt-2.5 grid grid-cols-[1.35fr_1fr] gap-2"
                                >
                                    <div
                                        class="dash-chart-bar rounded-xl p-2.5"
                                    >
                                        <div
                                            class="mb-2 flex items-center justify-between"
                                        >
                                            <span
                                                class="text-[8px] font-semibold text-white"
                                                >Daily Sales</span
                                            >
                                            <span
                                                class="text-[7px] text-white/50"
                                                >Last 7 days</span
                                            >
                                        </div>
                                        <div
                                            class="flex h-[56px] items-end gap-1"
                                        >
                                            <span
                                                v-for="h in [
                                                    35, 62, 48, 78, 55, 88, 40,
                                                    65, 52, 70, 38,
                                                ]"
                                                :key="h"
                                                class="flex-1 rounded-t bg-white"
                                                :style="{ height: h + '%' }"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-2.5 shadow-sm"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="text-[8px] font-bold text-slate-800"
                                                >Revenue Overview</span
                                            >
                                            <span
                                                class="text-[7px] text-slate-400"
                                                >Monthly</span
                                            >
                                        </div>
                                        <svg
                                            viewBox="0 0 120 52"
                                            class="mt-1 h-[56px] w-full"
                                        >
                                            <path
                                                d="M0 38 L18 30 L36 33 L54 18 L72 24 L90 12 L108 16 L120 10"
                                                fill="none"
                                                stroke="#2563eb"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M0 38 L18 30 L36 33 L54 18 L72 24 L90 12 L108 16 L120 10 L120 52 L0 52 Z"
                                                fill="#2563eb"
                                                opacity="0.08"
                                            />
                                            <path
                                                d="M0 38 L18 30 L36 33 L54 18 L72 24 L90 12 L108 16 L120 10"
                                                fill="none"
                                                stroke="#7c3aed"
                                                stroke-width="1.2"
                                                opacity="0.35"
                                            />
                                        </svg>
                                    </div>
                                </div>
                                <!-- Best selling -->
                                <div class="mt-2.5">
                                    <div
                                        class="mb-1.5 text-[8px] font-bold text-slate-800"
                                    >
                                        Best Selling Products
                                    </div>
                                    <div class="grid grid-cols-4 gap-1.5">
                                        <div
                                            v-for="i in 4"
                                            :key="i"
                                            class="overflow-hidden rounded-lg border border-slate-100 bg-white"
                                        >
                                            <div
                                                class="aspect-[4/3] bg-gradient-to-br from-slate-50 to-slate-100 p-1"
                                            >
                                                <div
                                                    class="grid h-full place-items-center rounded bg-white text-[16px]"
                                                >
                                                    {{
                                                        [
                                                            '🍅',
                                                            '🥩',
                                                            '🧀',
                                                            '🍝',
                                                        ][i - 1]
                                                    }}
                                                </div>
                                            </div>
                                            <div class="px-1.5 py-1">
                                                <div
                                                    class="h-1 w-3/4 rounded bg-slate-200"
                                                />
                                                <div
                                                    class="mt-1 h-1 w-1/2 rounded bg-slate-100"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="device-laptop-base" />
                <div
                    class="mx-auto mt-4 h-4 w-[88%] rounded-full bg-slate-900/10 blur-xl"
                />
            </motion.div>

            <!-- PHONE — front right -->
            <motion.div
                class="device-shell animate-float-phone relative z-20 hidden w-[22%] max-w-[200px] shrink-0 -translate-y-1 sm:block lg:w-[17%]"
                :initial="{ opacity: 0, y: 40, rotateY: -12 }"
                :animate="{ opacity: 1, y: 0, rotateY: 8 }"
                :transition="{
                    duration: 1.1,
                    ease: [0.16, 1, 0.3, 1],
                    delay: 0.25,
                }"
                :while-hover="{ rotateY: 2, y: -6 }"
            >
                <div class="device-phone-frame">
                    <div class="device-screen-phone overflow-hidden bg-white">
                        <img
                            v-if="phoneSrc"
                            :src="phoneSrc"
                            alt="Mobile preview"
                            class="h-full w-full object-cover object-top"
                            loading="eager"
                        />
                        <div v-else class="bg-white">
                            <div
                                class="flex items-center justify-between px-3 py-2"
                            >
                                <span
                                    class="text-[8px] font-bold text-[#2563eb]"
                                    >OUTLET EXPRESS</span
                                >
                                <span
                                    class="size-4 rounded-full bg-slate-100"
                                />
                            </div>
                            <div class="px-2 py-1">
                                <div class="grid grid-cols-2 gap-1">
                                    <div
                                        class="dash-card-purple rounded-lg p-2 text-white"
                                    >
                                        <div class="text-[7px] opacity-80">
                                            Sales
                                        </div>
                                        <div class="text-[11px] font-bold">
                                            ৳ 48k
                                        </div>
                                    </div>
                                    <div
                                        class="dash-card-blue rounded-lg p-2 text-white"
                                    >
                                        <div class="text-[7px] opacity-80">
                                            Orders
                                        </div>
                                        <div class="text-[11px] font-bold">
                                            284
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-1.5 grid grid-cols-2 gap-1">
                                    <div
                                        class="dash-card-orange rounded-lg p-2 text-white"
                                    >
                                        <div class="text-[7px] opacity-80">
                                            Revenue
                                        </div>
                                        <div class="text-[9px] font-bold">
                                            ৳ 12k
                                        </div>
                                    </div>
                                    <div
                                        class="dash-card-green rounded-lg p-2 text-white"
                                    >
                                        <div class="text-[7px] opacity-80">
                                            Users
                                        </div>
                                        <div class="text-[9px] font-bold">
                                            892
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="mt-2 rounded-xl border border-slate-100 p-2"
                                >
                                    <div
                                        class="text-[7px] font-bold text-slate-700"
                                    >
                                        Revenue Overview
                                    </div>
                                    <svg
                                        viewBox="0 0 80 28"
                                        class="mt-1 h-10 w-full"
                                    >
                                        <path
                                            d="M0 18 L12 12 L24 15 L36 8 L48 11 L64 6 L80 9"
                                            fill="none"
                                            stroke="#2563eb"
                                            stroke-width="1.6"
                                        />
                                        <path
                                            d="M0 18 L12 12 L24 15 L36 8 L48 11 L64 6 L80 9 L80 28 L0 28 Z"
                                            fill="#2563eb"
                                            opacity="0.08"
                                        />
                                    </svg>
                                </div>
                                <div class="mt-2 space-y-1.5">
                                    <div
                                        v-for="i in 3"
                                        :key="i"
                                        class="flex items-center gap-2 rounded-lg border border-slate-100 p-1.5"
                                    >
                                        <div
                                            class="grid size-6 place-items-center rounded bg-slate-50 text-[12px]"
                                        >
                                            {{ ['🍕', '🍔', '🥗'][i - 1] }}
                                        </div>
                                        <div class="flex-1">
                                            <div
                                                class="h-1 w-10 rounded bg-slate-200"
                                            />
                                            <div
                                                class="mt-1 h-1 w-6 rounded bg-slate-100"
                                            />
                                        </div>
                                        <div
                                            class="h-1.5 w-8 rounded bg-emerald-100"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="mx-auto mt-2 h-1 w-12 rounded-full bg-slate-900"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="mx-auto mt-3 h-2.5 w-[70%] rounded-full bg-slate-900/10 blur-xl"
                />
            </motion.div>
        </div>

        <!-- Mobile stacked fallback is handled via hidden sm:block already; tablet/phone hide on mobile to keep laptop hero clean -->
        <p
            class="mx-auto mt-6 max-w-xl text-center text-xs leading-relaxed text-slate-400 sm:text-sm"
        >
            Responsive — every screen, every device, in perfect sync. Matches
            the live Outlet Express dashboard.
        </p>
    </div>
</template>
