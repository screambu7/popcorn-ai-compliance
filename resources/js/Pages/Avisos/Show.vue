<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ aviso: Object });
const a = props.aviso;
const estadoForm = useForm({ estado: a.estado });
const updateEstado = () => estadoForm.put(route('avisos.updateEstado', a.id));
const approveForm = useForm({});
const approve = () => approveForm.post(route('avisos.approve', a.id));
const estadoColor = (e) => ({ borrador: 'bg-slate-100 text-slate-600', generado: 'bg-blue-100 text-blue-700', enviado: 'bg-yellow-100 text-yellow-700', aceptado: 'bg-green-100 text-green-700', rechazado: 'bg-red-100 text-red-700' }[e] || 'bg-slate-100');
</script>

<template>
    <Head :title="`Aviso ${a.folio}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('avisos.index')" class="text-slate-400 hover:text-slate-600"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg></Link>
                <h1 class="text-xl font-bold text-slate-900 font-mono">{{ a.folio }}</h1>
                <span :class="estadoColor(a.estado)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">{{ a.estado }}</span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 capitalize">{{ a.tipo }}</span>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Detalle del Aviso</h3>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div><p class="text-xs text-slate-500">Periodo</p><p class="text-sm font-medium text-slate-900">{{ String(a.periodo_mes).padStart(2,'0') }}/{{ a.periodo_anio }}</p></div>
                        <div><p class="text-xs text-slate-500">Total Operaciones</p><p class="text-lg font-bold text-slate-900">{{ a.total_operaciones }}</p></div>
                        <div><p class="text-xs text-slate-500">Monto Total</p><p class="text-lg font-bold font-mono text-slate-900">${{ Number(a.monto_total).toLocaleString() }}</p></div>
                        <div><p class="text-xs text-slate-500">Generado</p><p class="text-sm text-slate-900">{{ a.fecha_generacion ? new Date(a.fecha_generacion).toLocaleDateString('es-MX') : '—' }}</p></div>
                        <div><p class="text-xs text-slate-500">Enviado</p><p class="text-sm text-slate-900">{{ a.fecha_envio ? new Date(a.fecha_envio).toLocaleDateString('es-MX') : '—' }}</p></div>
                        <div><p class="text-xs text-slate-500">Acuse</p><p class="text-sm text-slate-900">{{ a.fecha_acuse ? new Date(a.fecha_acuse).toLocaleDateString('es-MX') : '—' }}</p></div>
                    </div>
                </div>

                <!-- Operaciones linked -->
                <div v-if="a.operaciones?.length" class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
                    <div class="border-b border-slate-100 px-6 py-4"><h3 class="text-sm font-semibold text-slate-900">Operaciones Incluidas</h3></div>
                    <div class="divide-y divide-slate-100">
                        <div v-for="op in a.operaciones" :key="op.id" class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <Link :href="route('operaciones.show', op.id)" class="font-mono text-sm font-medium text-slate-900 hover:text-popcorn-600">{{ op.folio }}</Link>
                                <p class="text-xs text-slate-500">{{ op.client?.razon_social }}</p>
                            </div>
                            <span class="font-mono text-sm text-slate-900">${{ Number(op.monto).toLocaleString() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Acciones</h3>
                    <div class="space-y-3">
                        <form @submit.prevent="updateEstado" class="space-y-2">
                            <select v-model="estadoForm.estado" class="block w-full rounded-lg border-slate-300 text-sm">
                                <option value="borrador">Borrador</option><option value="generado">Generado</option>
                                <option value="enviado">Enviado al SAT</option><option value="aceptado">Aceptado</option><option value="rechazado">Rechazado</option>
                            </select>
                            <button type="submit" class="w-full rounded-lg bg-popcorn-500 px-4 py-2 text-sm font-semibold text-white hover:bg-popcorn-600">Actualizar Estado</button>
                        </form>
                        <button v-if="!a.aprobado_por_user_id && a.estado === 'generado'" @click="approve" class="w-full rounded-lg border-2 border-green-500 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">
                            Aprobar como Oficial
                        </button>
                        <a v-if="a.xml_path" :href="route('avisos.downloadXml', a.id)" class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Descargar XML para SPPLD
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
