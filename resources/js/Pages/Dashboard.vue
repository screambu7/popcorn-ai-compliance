<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    expedientes_recientes: Array,
    clientes_alto_riesgo: Array,
});

const statCards = [
    { label: 'Clientes Activos', key: 'clientes_activos', color: 'bg-blue-500' },
    { label: 'Actividad Vulnerable', key: 'clientes_vulnerables', color: 'bg-yellow-500' },
    { label: 'Alto Riesgo', key: 'clientes_alto_riesgo', color: 'bg-red-500' },
    { label: 'Expedientes Pendientes', key: 'expedientes_pendientes', color: 'bg-orange-500' },
    { label: 'Expedientes Alto Riesgo', key: 'expedientes_alto_riesgo', color: 'bg-red-600' },
    { label: 'Requieren Reporte UIF', key: 'expedientes_reporte_uif', color: 'bg-purple-600' },
];

const riesgoColor = (nivel) => ({
    bajo: 'bg-green-100 text-green-800',
    medio: 'bg-yellow-100 text-yellow-800',
    alto: 'bg-red-100 text-red-800',
}[nivel] || 'bg-gray-100 text-gray-800');

const estadoColor = (estado) => ({
    pendiente: 'bg-yellow-100 text-yellow-800',
    en_revision: 'bg-blue-100 text-blue-800',
    completo: 'bg-green-100 text-green-800',
    reportado_uif: 'bg-purple-100 text-purple-800',
}[estado] || 'bg-gray-100 text-gray-800');
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard — Popcorn Compliance
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stat Cards -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6 mb-8">
                    <div v-for="card in statCards" :key="card.key"
                        class="overflow-hidden rounded-lg bg-white shadow">
                        <div :class="card.color" class="h-1"></div>
                        <div class="p-4">
                            <p class="text-sm text-gray-500">{{ card.label }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats?.[card.key] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Expedientes Recientes -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Expedientes Recientes</h3>
                            <Link :href="route('expedientes.index')" class="text-sm text-orange-600 hover:text-orange-800">
                                Ver todos
                            </Link>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div v-for="exp in expedientes_recientes" :key="exp.id"
                                class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <Link :href="route('expedientes.show', exp.id)" class="font-medium text-gray-900 hover:text-orange-600">
                                        {{ exp.folio }}
                                    </Link>
                                    <p class="text-sm text-gray-500">{{ exp.client?.razon_social }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="riesgoColor(exp.nivel_riesgo)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        {{ exp.nivel_riesgo }}
                                    </span>
                                    <span :class="estadoColor(exp.estado)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        {{ exp.estado }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="!expedientes_recientes?.length" class="px-6 py-8 text-center text-gray-500">
                                No hay expedientes registrados
                            </div>
                        </div>
                    </div>

                    <!-- Clientes Alto Riesgo -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Clientes Alto Riesgo</h3>
                            <Link :href="route('clients.index')" class="text-sm text-orange-600 hover:text-orange-800">
                                Ver todos
                            </Link>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div v-for="client in clientes_alto_riesgo" :key="client.id"
                                class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <Link :href="route('clients.show', client.id)" class="font-medium text-gray-900 hover:text-orange-600">
                                        {{ client.razon_social }}
                                    </Link>
                                    <p class="text-sm text-gray-500">{{ client.rfc }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span v-if="client.es_pep" class="inline-flex items-center rounded-full bg-purple-100 text-purple-800 px-2.5 py-0.5 text-xs font-medium">
                                        PEP
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-red-100 text-red-800 px-2.5 py-0.5 text-xs font-medium">
                                        Alto Riesgo
                                    </span>
                                </div>
                            </div>
                            <div v-if="!clientes_alto_riesgo?.length" class="px-6 py-8 text-center text-gray-500">
                                Sin clientes de alto riesgo
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
