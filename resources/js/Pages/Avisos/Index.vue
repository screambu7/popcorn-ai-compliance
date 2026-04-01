<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ avisos: Object, filters: Object });
const estado = ref(props.filters?.estado || '');
watch(estado, (v) => router.get(route('avisos.index'), { estado: v }, { preserveState: true, replace: true }));

const generateForm = useForm({ mes: new Date().getMonth() + 1, anio: new Date().getFullYear() });
const showGenerate = ref(false);
const generate = () => generateForm.post(route('avisos.generate'), { onSuccess: () => showGenerate.value = false });

const estadoColor = (e) => ({ borrador: 'bg-slate-100 text-slate-600', generado: 'bg-blue-100 text-blue-700', enviado: 'bg-yellow-100 text-yellow-700', aceptado: 'bg-green-100 text-green-700', rechazado: 'bg-red-100 text-red-700' }[e] || 'bg-slate-100');
const tipoColor = (t) => ({ normal: 'bg-blue-50 text-blue-700', cero: 'bg-slate-50 text-slate-600', '24horas': 'bg-red-50 text-red-700', modificatorio: 'bg-purple-50 text-purple-700' }[t] || 'bg-slate-50');
</script>

<template>
    <Head title="Avisos SPPLD" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-xl font-bold text-slate-900">Avisos SPPLD</h1>
                <button @click="showGenerate = !showGenerate" class="inline-flex items-center gap-2 rounded-lg bg-popcorn-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-popcorn-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    Generar Aviso
                </button>
            </div>
        </template>

        <!-- Generate form -->
        <div v-if="showGenerate" class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Generar Aviso Mensual</h3>
            <form @submit.prevent="generate" class="flex items-end gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500">Mes</label>
                    <select v-model="generateForm.mes" class="mt-1 rounded-lg border-slate-300 text-sm">
                        <option v-for="m in 12" :key="m" :value="m">{{ new Date(2026, m-1).toLocaleString('es-MX', { month: 'long' }) }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500">Año</label>
                    <input v-model.number="generateForm.anio" type="number" class="mt-1 w-24 rounded-lg border-slate-300 text-sm" />
                </div>
                <button type="submit" :disabled="generateForm.processing" class="rounded-lg bg-popcorn-500 px-4 py-2 text-sm font-semibold text-white hover:bg-popcorn-600 disabled:opacity-50">
                    {{ generateForm.processing ? 'Generando...' : 'Generar' }}
                </button>
            </form>
            <p class="mt-2 text-xs text-slate-400">Se recopilarán todas las operaciones del periodo que superen el umbral de aviso.</p>
        </div>

        <div class="mb-6">
            <select v-model="estado" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-popcorn-500 focus:ring-popcorn-500">
                <option value="">Todos</option>
                <option value="borrador">Borrador</option><option value="generado">Generado</option>
                <option value="enviado">Enviado</option><option value="aceptado">Aceptado</option><option value="rechazado">Rechazado</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Folio</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Periodo</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">Operaciones</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-500">Monto Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fecha</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="aviso in avisos.data" :key="aviso.id" class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3"><Link :href="route('avisos.show', aviso.id)" class="font-mono text-sm font-medium text-slate-900 hover:text-popcorn-600">{{ aviso.folio }}</Link></td>
                        <td class="px-4 py-3"><span :class="tipoColor(aviso.tipo)" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize">{{ aviso.tipo }}</span></td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ String(aviso.periodo_mes).padStart(2,'0') }}/{{ aviso.periodo_anio }}</td>
                        <td class="px-4 py-3 text-right text-sm font-mono text-slate-900">{{ aviso.total_operaciones }}</td>
                        <td class="px-4 py-3 text-right text-sm font-mono text-slate-900">${{ Number(aviso.monto_total).toLocaleString() }}</td>
                        <td class="px-4 py-3"><span :class="estadoColor(aviso.estado)" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize">{{ aviso.estado }}</span></td>
                        <td class="px-4 py-3 text-sm text-slate-500">{{ aviso.fecha_generacion ? new Date(aviso.fecha_generacion).toLocaleDateString('es-MX') : '' }}</td>
                    </tr>
                    <tr v-if="!avisos.data?.length"><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-400">Sin avisos generados</td></tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
