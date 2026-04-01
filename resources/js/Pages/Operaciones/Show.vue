<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ operacion: Object, uma_diario: Number });
const op = props.operacion;
const formatMoney = (v) => v ? `$${Number(v).toLocaleString('es-MX', { minimumFractionDigits: 2 })}` : '—';
const formatDate = (d) => d ? new Date(d).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' }) : '—';
</script>

<template>
    <Head :title="`Operación ${op.folio}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('operaciones.index')" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                </Link>
                <h1 class="text-xl font-bold text-slate-900 font-mono">{{ op.folio }}</h1>
                <span :class="op.supera_umbral_aviso ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">{{ op.estado }}</span>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Datos de la Operación</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-xs text-slate-500">Cliente</p>
                            <Link v-if="op.client" :href="route('clients.show', op.client.id)" class="text-sm font-medium text-slate-900 hover:text-popcorn-600">{{ op.client.razon_social }}</Link></div>
                        <div><p class="text-xs text-slate-500">Actividad</p><p class="text-sm text-slate-900">{{ op.tipo_actividad_vulnerable }}</p></div>
                        <div><p class="text-xs text-slate-500">Monto</p><p class="text-lg font-bold font-mono text-slate-900">{{ formatMoney(op.monto) }}</p></div>
                        <div><p class="text-xs text-slate-500">UMAs</p><p class="text-lg font-bold font-mono" :class="op.supera_umbral_aviso ? 'text-red-600' : 'text-green-600'">{{ op.umas_equivalente?.toLocaleString() }}</p></div>
                        <div><p class="text-xs text-slate-500">Forma de Pago</p><p class="text-sm capitalize text-slate-900">{{ op.forma_pago }}</p></div>
                        <div><p class="text-xs text-slate-500">Fecha</p><p class="text-sm text-slate-900">{{ formatDate(op.fecha_operacion) }}</p></div>
                    </div>
                </div>
                <div v-if="op.contraparte_nombre" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Contraparte</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-xs text-slate-500">Nombre</p><p class="text-sm text-slate-900">{{ op.contraparte_nombre }}</p></div>
                        <div><p class="text-xs text-slate-500">RFC</p><p class="text-sm font-mono text-slate-900">{{ op.contraparte_rfc || '—' }}</p></div>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Umbrales</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Identificación</span><span :class="op.supera_umbral_identificacion ? 'text-blue-600 font-medium' : 'text-slate-400'">{{ op.supera_umbral_identificacion ? 'Superado' : 'OK' }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Aviso SAT</span><span :class="op.supera_umbral_aviso ? 'text-yellow-600 font-medium' : 'text-slate-400'">{{ op.supera_umbral_aviso ? 'Superado' : 'OK' }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Límite efectivo</span><span :class="op.supera_umbral_efectivo ? 'text-red-600 font-medium' : 'text-slate-400'">{{ op.supera_umbral_efectivo ? 'EXCEDIDO' : 'OK' }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
