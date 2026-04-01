<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ operaciones: Object, filters: Object });

const search = ref(props.filters?.search || '');
const estado = ref(props.filters?.estado || '');

let timeout = null;
const applyFilters = () => {
    router.get(route('operaciones.index'), { search: search.value, estado: estado.value }, { preserveState: true, replace: true });
};
watch(search, () => { clearTimeout(timeout); timeout = setTimeout(applyFilters, 300); });
watch(estado, applyFilters);

const riesgoColor = (op) => {
    if (op.supera_umbral_efectivo) return 'bg-red-100 text-red-700';
    if (op.supera_umbral_aviso) return 'bg-yellow-100 text-yellow-700';
    if (op.supera_umbral_identificacion) return 'bg-blue-100 text-blue-700';
    return 'bg-green-100 text-green-700';
};
const riesgoLabel = (op) => {
    if (op.supera_umbral_efectivo) return 'Efectivo';
    if (op.supera_umbral_aviso) return 'Aviso';
    if (op.supera_umbral_identificacion) return 'Identificación';
    return 'Normal';
};
const estadoColor = (e) => ({ registrada: 'bg-slate-100 text-slate-600', identificada: 'bg-blue-100 text-blue-700', notificada: 'bg-yellow-100 text-yellow-700', reportada: 'bg-green-100 text-green-700' }[e] || 'bg-slate-100 text-slate-600');
</script>

<template>
    <Head title="Operaciones" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-xl font-bold text-slate-900">Operaciones</h1>
                <Link :href="route('operaciones.create')" class="inline-flex items-center gap-2 rounded-lg bg-popcorn-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-popcorn-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nueva Operación
                </Link>
            </div>
        </template>

        <div class="mb-6 flex flex-col gap-3 sm:flex-row">
            <input v-model="search" type="text" placeholder="Buscar por folio, cliente..." class="flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-popcorn-500 focus:ring-popcorn-500" />
            <select v-model="estado" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-popcorn-500 focus:ring-popcorn-500">
                <option value="">Todos los estados</option>
                <option value="registrada">Registrada</option>
                <option value="identificada">Identificada</option>
                <option value="notificada">Notificada</option>
                <option value="reportada">Reportada</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Folio</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Actividad</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">Monto</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">UMAs</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Umbral</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="op in operaciones.data" :key="op.id" class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <Link :href="route('operaciones.show', op.id)" class="font-mono text-sm font-medium text-slate-900 hover:text-popcorn-600">{{ op.folio }}</Link>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            <Link v-if="op.client" :href="route('clients.show', op.client.id)" class="hover:text-popcorn-600">{{ op.client.razon_social }}</Link>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ op.tipo_actividad_vulnerable }}</td>
                        <td class="px-4 py-3 text-right text-sm font-mono text-slate-900">${{ Number(op.monto).toLocaleString() }}</td>
                        <td class="px-4 py-3 text-right text-sm font-mono">{{ op.umas_equivalente?.toLocaleString() }}</td>
                        <td class="px-4 py-3">
                            <span :class="riesgoColor(op)" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium">{{ riesgoLabel(op) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="estadoColor(op.estado)" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize">{{ op.estado }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ op.fecha_operacion ? new Date(op.fecha_operacion).toLocaleDateString('es-MX') : '' }}</td>
                    </tr>
                    <tr v-if="!operaciones.data?.length">
                        <td colspan="8" class="px-4 py-12 text-center text-sm text-slate-400">Sin operaciones registradas</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="operaciones.links?.length > 3" class="border-t border-slate-100 px-4 py-3 flex items-center justify-between">
                <p class="text-xs text-slate-500">{{ operaciones.from }}–{{ operaciones.to }} de {{ operaciones.total }}</p>
                <div class="flex gap-1">
                    <Link v-for="link in operaciones.links" :key="link.label" :href="link.url || '#'"
                        :class="[link.active ? 'bg-popcorn-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-50', !link.url ? 'opacity-40 pointer-events-none' : '']"
                        class="inline-flex items-center rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium" v-html="link.label" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
