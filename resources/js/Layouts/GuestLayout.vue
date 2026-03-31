<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Left Panel — Branding -->
        <div class="hidden lg:flex lg:w-1/2 lg:flex-col lg:justify-between bg-slate-950 p-12">
            <div>
                <Link href="/" class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-popcorn-500 to-popcorn-600 shadow-lg shadow-popcorn-500/25">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">
                        <span class="text-popcorn-500">Popcorn</span>
                        <span class="text-white"> Compliance</span>
                    </span>
                </Link>
            </div>

            <div class="relative">
                <div class="absolute -top-32 -left-12 h-64 w-64 rounded-full bg-popcorn-500/5 blur-3xl"></div>
                <div class="absolute -bottom-16 right-0 h-48 w-48 rounded-full bg-popcorn-600/5 blur-3xl"></div>
                <div class="relative">
                    <h1 class="text-4xl font-bold tracking-tight text-white">
                        Compliance Fiscal e
                        <span class="bg-gradient-to-r from-popcorn-400 to-popcorn-600 bg-clip-text text-transparent">Inteligencia Antilavado</span>
                    </h1>
                    <p class="mt-4 text-lg text-slate-400">
                        Automatiza obligaciones fiscales, gestiona expedientes KYC y cumple con la LFPIORPI desde una sola plataforma.
                    </p>

                    <div class="mt-10 grid grid-cols-2 gap-4">
                        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
                            <p class="text-2xl font-bold text-popcorn-500">14</p>
                            <p class="text-xs text-slate-500">Actividades vulnerables</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
                            <p class="text-2xl font-bold text-popcorn-500">$117.31</p>
                            <p class="text-xs text-slate-500">UMA diario 2026</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
                            <p class="text-2xl font-bold text-popcorn-500">645</p>
                            <p class="text-xs text-slate-500">UMAs umbral aviso</p>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
                            <p class="text-2xl font-bold text-popcorn-500">10 años</p>
                            <p class="text-xs text-slate-500">Retención legal</p>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-xs text-slate-600">
                Conforme a LFPIORPI Art. 17 y 18 | Popcorn AI Compliance
            </p>
        </div>

        <!-- Right Panel — Form -->
        <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16 xl:px-24 bg-white">
            <!-- Mobile logo -->
            <div class="mb-8 lg:hidden">
                <Link href="/" class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-popcorn-500 to-popcorn-600">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">
                        <span class="text-popcorn-500">Popcorn</span>
                        <span class="text-slate-700"> Compliance</span>
                    </span>
                </Link>
            </div>

            <div class="mx-auto w-full max-w-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
