<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ results: Object, filters: Object });
const estado = ref(props.filters?.estado || '');
watch(estado, (v) => router.get(route('screening.index'), { estado: v }, { preserveState: true, replace: true }));

const listaColor = (t) => ({ sat_69b_definitivo: 'bg-red-100 text-red-700', sat_69b_presunto: 'bg-orange-100 text-orange-700', ofac_sdn: 'bg-red-100 text-red-700', uif_bloqueados: 'bg-red-100 text-red-700', pep: 'bg-purple-100 text-purple-700' }[t] || 'bg-slate-100 text-slate-600');
const estadoColor = (e) => ({ pendiente_revision: 'bg-yellow-100 text-yellow-700', confirmado: 'bg-red-100 text-red-700', descartado: 'bg-slate-100 text-slate-600', falso_positivo: 'bg-green-100 text-green-700' }[e] || 'bg-slate-100');

const reviewForm = useForm({ estado: '' });
const review = (result, newEstado) => {
    reviewForm.estado = newEstado;
    reviewForm.put(route('screening.review', result.id));
};
</script>

<template>
    <Head title="Screening Listas Negras" />
    <AuthenticatedLayout>
        <template #header><h1 class="text-xl font-bold text-slate-900">Screening de Listas Negras</h1></template>

        <div class="mb-6">
            <select v-model="estado" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-popcorn-500 focus:ring-popcorn-500">
                <option value="">Todos</option>
                <option value="pendiente_revision">Pendiente Revisión</option>
                <option value="confirmado">Confirmado</option>
                <option value="descartado">Descartado</option>
                <option value="falso_positivo">Falso Positivo</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Lista</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Match</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">Score</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">24h</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">Acciones</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="r in results.data" :key="r.id" class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <Link v-if="r.client" :href="route('clients.show', r.client.id)" class="text-sm font-medium text-slate-900 hover:text-popcorn-600">{{ r.client.razon_social }}</Link>
                            <p class="text-xs text-slate-400 font-mono">{{ r.client?.rfc }}</p>
                        </td>
                        <td class="px-4 py-3"><span :class="listaColor(r.lista_tipo)" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium">{{ r.lista_tipo?.replace(/_/g, ' ') }}</span></td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ r.match_nombre }}<span v-if="r.match_rfc" class="ml-1 font-mono text-xs text-slate-400">({{ r.match_rfc }})</span></td>
                        <td class="px-4 py-3 text-right"><span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold" :class="r.match_score >= 80 ? 'bg-red-100 text-red-700' : r.match_score >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-600'">{{ r.match_score }}%</span></td>
                        <td class="px-4 py-3"><span :class="estadoColor(r.estado)" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium">{{ r.estado?.replace(/_/g, ' ') }}</span></td>
                        <td class="px-4 py-3"><span v-if="r.requiere_aviso_24h" class="inline-flex items-center rounded-full bg-red-500 text-white px-2 py-0.5 text-xs font-bold">24H</span></td>
                        <td class="px-4 py-3 text-right" v-if="r.estado === 'pendiente_revision'">
                            <div class="flex justify-end gap-1">
                                <button @click="review(r, 'confirmado')" class="rounded px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Confirmar</button>
                                <button @click="review(r, 'falso_positivo')" class="rounded px-2 py-1 text-xs font-medium text-green-600 hover:bg-green-50">Falso +</button>
                                <button @click="review(r, 'descartado')" class="rounded px-2 py-1 text-xs font-medium text-slate-500 hover:bg-slate-50">Descartar</button>
                            </div>
                        </td>
                        <td v-else class="px-4 py-3 text-right text-xs text-slate-400">{{ r.revisado_at ? new Date(r.revisado_at).toLocaleDateString('es-MX') : '' }}</td>
                    </tr>
                    <tr v-if="!results.data?.length"><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-400">Sin resultados de screening</td></tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
