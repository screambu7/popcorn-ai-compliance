<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ clients: Array, actividades_vulnerables: Object, uma_diario: Number });

const form = useForm({
    client_id: '', tipo_actividad_vulnerable: '', descripcion: '', monto: '', moneda: 'MXN',
    forma_pago: 'transferencia', monto_efectivo: '', contraparte_nombre: '', contraparte_rfc: '',
    fecha_operacion: new Date().toISOString().split('T')[0], notas: '',
});

const umas = computed(() => form.monto ? Math.round(form.monto / props.uma_diario) : 0);
const superaId = computed(() => umas.value >= 500);
const superaAviso = computed(() => umas.value >= 645);
const superaEfectivo = computed(() => {
    if (!form.monto_efectivo) return false;
    return Math.round(form.monto_efectivo / props.uma_diario) >= 645;
});

const submit = () => form.post(route('operaciones.store'));
</script>

<template>
    <Head title="Nueva Operación" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('operaciones.index')" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                </Link>
                <h1 class="text-xl font-bold text-slate-900">Nueva Operación</h1>
            </div>
        </template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <form @submit.prevent="submit" class="lg:col-span-2 space-y-6">
                <!-- Datos principales -->
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Datos de la Operación</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Cliente</label>
                            <select v-model="form.client_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-popcorn-500 focus:ring-popcorn-500">
                                <option value="">Seleccionar...</option>
                                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.razon_social }} ({{ c.rfc }})</option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.client_id" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Actividad Vulnerable (Art. 17)</label>
                            <select v-model="form.tipo_actividad_vulnerable" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-popcorn-500 focus:ring-popcorn-500">
                                <option value="">Seleccionar...</option>
                                <option v-for="(nombre, clave) in actividades_vulnerables" :key="clave" :value="clave">{{ clave }}. {{ nombre }}</option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.tipo_actividad_vulnerable" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Monto</label>
                            <div class="relative mt-1"><span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">$</span>
                                <input v-model.number="form.monto" type="number" step="0.01" class="block w-full rounded-lg border-slate-300 pl-7 text-sm focus:border-popcorn-500 focus:ring-popcorn-500" />
                            </div>
                            <InputError class="mt-1" :message="form.errors.monto" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Forma de Pago</label>
                            <select v-model="form.forma_pago" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-popcorn-500 focus:ring-popcorn-500">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div v-if="form.forma_pago === 'efectivo'">
                            <label class="block text-sm font-medium text-slate-700">Monto en Efectivo</label>
                            <div class="relative mt-1"><span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">$</span>
                                <input v-model.number="form.monto_efectivo" type="number" step="0.01" class="block w-full rounded-lg border-slate-300 pl-7 text-sm focus:border-popcorn-500 focus:ring-popcorn-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Fecha Operación</label>
                            <input v-model="form.fecha_operacion" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-popcorn-500 focus:ring-popcorn-500" />
                        </div>
                    </div>
                </div>

                <!-- Contraparte -->
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Contraparte</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div><label class="block text-sm font-medium text-slate-700">Nombre</label>
                            <input v-model="form.contraparte_nombre" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-popcorn-500 focus:ring-popcorn-500" /></div>
                        <div><label class="block text-sm font-medium text-slate-700">RFC</label>
                            <input v-model="form.contraparte_rfc" type="text" maxlength="13" class="mt-1 block w-full rounded-lg border-slate-300 text-sm font-mono uppercase focus:border-popcorn-500 focus:ring-popcorn-500" /></div>
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <label class="block text-sm font-medium text-slate-700">Notas</label>
                    <textarea v-model="form.notas" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-popcorn-500 focus:ring-popcorn-500"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('operaciones.index')" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancelar</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-popcorn-500 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-popcorn-600 disabled:opacity-50">
                        {{ form.processing ? 'Guardando...' : 'Registrar Operación' }}
                    </button>
                </div>
            </form>

            <!-- Sidebar: UMA calculator -->
            <div class="space-y-4">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Calculadora UMA</h3>
                    <div class="text-center">
                        <p class="text-4xl font-bold" :class="superaAviso ? 'text-red-600' : superaId ? 'text-yellow-600' : 'text-green-600'">{{ umas.toLocaleString() }}</p>
                        <p class="text-sm text-slate-500 mt-1">UMAs equivalentes</p>
                        <p class="text-xs text-slate-400 mt-0.5">UMA diario: ${{ uma_diario }}</p>
                    </div>
                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Identificación (500)</span>
                            <span :class="superaId ? 'text-blue-600 font-medium' : 'text-slate-400'">{{ superaId ? 'Superado' : 'OK' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Aviso SAT (645)</span>
                            <span :class="superaAviso ? 'text-yellow-600 font-medium' : 'text-slate-400'">{{ superaAviso ? 'Superado' : 'OK' }}</span>
                        </div>
                        <div v-if="form.forma_pago === 'efectivo'" class="flex items-center justify-between">
                            <span class="text-slate-500">Límite efectivo (645)</span>
                            <span :class="superaEfectivo ? 'text-red-600 font-medium' : 'text-slate-400'">{{ superaEfectivo ? 'EXCEDIDO' : 'OK' }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="superaEfectivo" class="rounded-xl border-2 border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-800">Límite de efectivo excedido</p>
                    <p class="text-xs text-red-600 mt-1">La LFPIORPI prohíbe recibir en efectivo montos que superen el umbral de 645 UMAs por actividad.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
