<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const features = [
    {
        title: 'Alertas Fiscales',
        description: 'Recordatorios automáticos de ISR, IVA, DIOT, contabilidad electrónica y declaración anual. Nunca pierdas una fecha límite.',
        icon: 'bell',
    },
    {
        title: 'KYC Antilavado',
        description: 'Expedientes de conocimiento del cliente conforme a la LFPIORPI Art. 17. Cálculo automático de umbrales UMA y detección de PEP.',
        icon: 'shield',
    },
    {
        title: 'Auditoría Completa',
        description: 'Registro de cada operación con trazabilidad total. Retención obligatoria de 10 años conforme al Art. 18 LFPIORPI.',
        icon: 'document',
    },
];

const stats = [
    { value: '14', label: 'Actividades vulnerables LFPIORPI' },
    { value: '$117.31', label: 'UMA diario 2026' },
    { value: '10 años', label: 'Retención obligatoria' },
    { value: '24/7', label: 'Monitoreo automatizado' },
];
</script>

<template>
    <Head title="Compliance Fiscal e Inteligencia Antilavado" />

    <div class="min-h-screen bg-slate-950">
        <!-- Nav -->
        <nav class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-8">
            <div class="flex items-center gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-popcorn-500 to-popcorn-600 shadow-lg shadow-popcorn-500/25">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight">
                    <span class="text-popcorn-500">Popcorn</span>
                    <span class="text-white"> Compliance</span>
                </span>
            </div>
            <div v-if="canLogin" class="flex items-center gap-4">
                <Link :href="route('login')" class="text-sm font-medium text-slate-300 transition hover:text-white">
                    Iniciar Sesión
                </Link>
                <Link v-if="canRegister" :href="route('register')" class="rounded-lg bg-popcorn-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-popcorn-500/25 transition hover:bg-popcorn-600">
                    Registrarse
                </Link>
            </div>
        </nav>

        <!-- Hero -->
        <div class="relative isolate overflow-hidden">
            <div class="absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-gradient-to-b from-popcorn-500/10 via-transparent to-transparent"></div>
                <div class="absolute right-0 top-0 h-[600px] w-[600px] -translate-y-1/4 translate-x-1/4 rounded-full bg-popcorn-500/5 blur-3xl"></div>
                <div class="absolute left-0 bottom-0 h-[400px] w-[400px] translate-y-1/4 -translate-x-1/4 rounded-full bg-popcorn-600/5 blur-3xl"></div>
            </div>

            <div class="mx-auto max-w-7xl px-6 pb-24 pt-16 sm:pb-32 lg:px-8 lg:pt-24">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-popcorn-500/20 bg-popcorn-500/10 px-4 py-1.5 text-sm text-popcorn-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-popcorn-500 animate-pulse"></span>
                        Plataforma de Compliance con IA
                    </div>

                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
                        Compliance Fiscal e
                        <span class="bg-gradient-to-r from-popcorn-400 to-popcorn-600 bg-clip-text text-transparent">Inteligencia Antilavado</span>
                    </h1>

                    <p class="mt-6 text-lg leading-8 text-slate-400 sm:text-xl">
                        Automatiza tus obligaciones fiscales, gestiona expedientes KYC conforme a la LFPIORPI y mantén el control total de tus clientes con actividades vulnerables.
                    </p>

                    <div class="mt-10 flex items-center justify-center gap-4">
                        <Link v-if="canRegister" :href="route('register')"
                            class="rounded-xl bg-popcorn-500 px-8 py-3.5 text-sm font-semibold text-white shadow-xl shadow-popcorn-500/25 transition-all hover:bg-popcorn-600 hover:shadow-popcorn-500/40 hover:-translate-y-0.5">
                            Comenzar Gratis
                        </Link>
                        <Link v-if="canLogin" :href="route('login')"
                            class="rounded-xl border border-slate-700 bg-slate-800/50 px-8 py-3.5 text-sm font-semibold text-slate-300 transition-all hover:border-slate-600 hover:bg-slate-800 hover:text-white">
                            Ya tengo cuenta
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="relative mx-auto max-w-7xl px-6 pb-24 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Todo lo que necesitas para cumplir</h2>
                <p class="mt-4 text-lg text-slate-400">Herramientas diseñadas para el marco regulatorio mexicano</p>
            </div>

            <div class="mx-auto mt-16 grid max-w-5xl grid-cols-1 gap-8 sm:grid-cols-3">
                <div v-for="feature in features" :key="feature.title"
                    class="group relative rounded-2xl border border-slate-800 bg-slate-900/50 p-8 transition-all hover:border-popcorn-500/30 hover:bg-slate-900">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-popcorn-500/10 text-popcorn-500 transition group-hover:bg-popcorn-500/20">
                        <svg v-if="feature.icon === 'bell'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <svg v-if="feature.icon === 'shield'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <svg v-if="feature.icon === 'document'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">{{ feature.title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-400">{{ feature.description }}</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="border-y border-slate-800 bg-slate-900/30">
            <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
                <div class="grid grid-cols-2 gap-8 sm:grid-cols-4">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <p class="text-3xl font-bold tracking-tight text-popcorn-500 sm:text-4xl">{{ stat.value }}</p>
                        <p class="mt-2 text-sm text-slate-400">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="mx-auto max-w-7xl px-6 py-24 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-popcorn-600 to-popcorn-500 px-8 py-16 text-center shadow-2xl shadow-popcorn-500/20 sm:px-16">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Protege tu negocio hoy
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-popcorn-100">
                    Cumple con tus obligaciones fiscales y antilavado de forma inteligente. Sin complicaciones.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <Link v-if="canRegister" :href="route('register')"
                        class="rounded-xl bg-white px-8 py-3.5 text-sm font-semibold text-popcorn-600 shadow-lg transition-all hover:bg-popcorn-50 hover:-translate-y-0.5">
                        Crear Cuenta
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-800">
            <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-popcorn-500/10">
                            <svg class="h-4 w-4 text-popcorn-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <span class="text-sm text-slate-500">Popcorn AI Compliance</span>
                    </div>
                    <p class="text-xs text-slate-600">
                        Conforme a LFPIORPI, Art. 17 y 18 | UMA 2026: $117.31/día
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
