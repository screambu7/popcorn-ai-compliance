<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    operaciones_por_mes: Array,
    semaforo_distribucion: Object,
    actividad_reciente: Array,
    expedientes_recientes: Array,
    clientes_alto_riesgo: Array,
});

const totalSemaforo = computed(() => {
    const s = props.semaforo_distribucion || { verde: 0, amarillo: 0, rojo: 0 };
    return s.verde + s.amarillo + s.rojo || 1;
});

const semaforoPercent = (color) => {
    return Math.round(((props.semaforo_distribucion?.[color] || 0) / totalSemaforo.value) * 100);
};

const maxOperaciones = computed(() => {
    return Math.max(...(props.operaciones_por_mes || []).map(o => o.count), 1);
});

const riesgoColor = (nivel) => ({
    bajo: 'bg-green-100 text-green-700',
    medio: 'bg-yellow-100 text-yellow-700',
    alto: 'bg-red-100 text-red-700',
}[nivel] || 'bg-slate-100 text-slate-600');

const estadoColor = (estado) => ({
    pendiente: 'bg-yellow-100 text-yellow-700',
    en_revision: 'bg-blue-100 text-blue-700',
    completo: 'bg-green-100 text-green-700',
    reportado_uif: 'bg-purple-100 text-purple-700',
}[estado] || 'bg-slate-100 text-slate-600');

const formatMoney = (v) => v ? `$${Number(v).toLocaleString('es-MX')}` : '$0';

const kpis = computed(() => [
    { label: 'Clientes Activos', value: props.stats?.clientes_activos ?? 0, icon: 'users', color: 'text-blue-600 bg-blue-50' },
    { label: 'Operaciones (Mes)', value: props.stats?.operaciones_mes_actual ?? 0, icon: 'currency', color: 'text-popcorn-600 bg-popcorn-50' },
    { label: 'Avisos Pendientes', value: props.stats?.avisos_pendientes ?? 0, icon: 'document', color: 'text-amber-600 bg-amber-50', alert: (props.stats?.avisos_pendientes ?? 0) > 0 },
    { label: 'Screening Pendiente', value: props.stats?.screening_pendientes ?? 0, icon: 'shield', color: 'text-red-600 bg-red-50', alert: (props.stats?.screening_pendientes ?? 0) > 0 },
    { label: 'Docs Vencidos', value: props.stats?.documentos_vencidos ?? 0, icon: 'paper', color: 'text-rose-600 bg-rose-50', alert: (props.stats?.documentos_vencidos ?? 0) > 0 },
    { label: 'Score Compliance', value: `${props.stats?.compliance_score ?? 100}%`, icon: 'check', color: 'text-emerald-600 bg-emerald-50' },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-bold text-slate-900">Dashboard</h1>
        </template>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div v-for="kpi in kpis" :key="kpi.label" class="relative overflow-hidden rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200/60">
                <div class="flex items-center gap-3">
                    <div :class="kpi.color" class="flex h-10 w-10 items-center justify-center rounded-lg">
                        <svg v-if="kpi.icon==='users'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        <svg v-if="kpi.icon==='currency'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <svg v-if="kpi.icon==='document'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        <svg v-if="kpi.icon==='shield'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        <svg v-if="kpi.icon==='paper'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                        <svg v-if="kpi.icon==='check'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ kpi.value }}</p>
                <p class="text-xs text-slate-500">{{ kpi.label }}</p>
                <div v-if="kpi.alert" class="absolute right-3 top-3 h-2.5 w-2.5 rounded-full bg-red-500 animate-pulse"></div>
            </div>
        </div>

        <!-- Middle section: Semáforo + Operaciones chart -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Semáforo -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                <h3 class="text-sm font-semibold text-slate-900">Semáforo de Cumplimiento</h3>
                <div class="mt-6 space-y-4">
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-green-500"></span>
                                <span class="text-slate-600">Verde</span>
                            </div>
                            <span class="font-semibold text-slate-900">{{ semaforo_distribucion?.verde ?? 0 }} <span class="font-normal text-slate-400">({{ semaforoPercent('verde') }}%)</span></span>
                        </div>
                        <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-green-500 transition-all" :style="{ width: semaforoPercent('verde') + '%' }"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                                <span class="text-slate-600">Amarillo</span>
                            </div>
                            <span class="font-semibold text-slate-900">{{ semaforo_distribucion?.amarillo ?? 0 }} <span class="font-normal text-slate-400">({{ semaforoPercent('amarillo') }}%)</span></span>
                        </div>
                        <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-yellow-400 transition-all" :style="{ width: semaforoPercent('amarillo') + '%' }"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-red-500"></span>
                                <span class="text-slate-600">Rojo</span>
                            </div>
                            <span class="font-semibold text-slate-900">{{ semaforo_distribucion?.rojo ?? 0 }} <span class="font-normal text-slate-400">({{ semaforoPercent('rojo') }}%)</span></span>
                        </div>
                        <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-red-500 transition-all" :style="{ width: semaforoPercent('rojo') + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operaciones por mes (bar chart) -->
            <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Operaciones (últimos 6 meses)</h3>
                    <Link :href="route('operaciones.index')" class="text-xs text-popcorn-600 hover:text-popcorn-500">Ver todas</Link>
                </div>
                <div class="mt-6 flex items-end gap-3" style="height: 160px">
                    <div v-for="op in operaciones_por_mes" :key="op.mes" class="flex flex-1 flex-col items-center gap-2">
                        <span class="text-xs font-semibold text-slate-900">{{ op.count }}</span>
                        <div class="w-full rounded-t-md bg-popcorn-500 transition-all hover:bg-popcorn-600"
                            :style="{ height: Math.max((op.count / maxOperaciones) * 120, 4) + 'px' }">
                        </div>
                        <span class="text-xs text-slate-500 truncate w-full text-center">{{ op.mes }}</span>
                    </div>
                    <div v-if="!operaciones_por_mes?.length" class="flex flex-1 items-center justify-center text-sm text-slate-400">
                        Sin datos aún
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom section: Activity feed + alerts -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Actividad reciente -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-slate-900">Actividad Reciente</h3>
                </div>
                <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                    <div v-for="log in actividad_reciente" :key="log.id" class="px-6 py-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">{{ log.accion }}</span>
                            <span class="text-xs text-slate-400">{{ new Date(log.created_at).toLocaleString('es-MX', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span v-if="log.client" class="text-xs text-slate-500">{{ log.client.razon_social }}</span>
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs bg-slate-100 text-slate-500">{{ log.canal }}</span>
                        </div>
                    </div>
                    <div v-if="!actividad_reciente?.length" class="px-6 py-12 text-center text-sm text-slate-400">
                        Sin actividad registrada
                    </div>
                </div>
            </div>

            <!-- Clientes alto riesgo -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Clientes Alto Riesgo</h3>
                    <Link :href="route('clients.index')" class="text-xs text-popcorn-600 hover:text-popcorn-500">Ver todos</Link>
                </div>
                <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                    <div v-for="client in clientes_alto_riesgo" :key="client.id" class="px-6 py-3 flex items-center justify-between hover:bg-slate-50 transition">
                        <div>
                            <Link :href="route('clients.show', client.id)" class="text-sm font-medium text-slate-700 hover:text-popcorn-600">
                                {{ client.razon_social }}
                            </Link>
                            <p class="text-xs text-slate-400 font-mono">{{ client.rfc }}</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span v-if="client.es_pep" class="inline-flex items-center rounded-full bg-purple-100 text-purple-700 px-2 py-0.5 text-xs font-medium">PEP</span>
                            <span class="inline-flex items-center rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-xs font-medium">Alto</span>
                        </div>
                    </div>
                    <div v-if="!clientes_alto_riesgo?.length" class="px-6 py-12 text-center text-sm text-slate-400">
                        Sin clientes de alto riesgo
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
