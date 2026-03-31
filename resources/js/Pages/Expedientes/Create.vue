<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    clients: Array,
    actividades_vulnerables: Object,
    uma_diario: Number,
    preselected_client_id: [Number, String],
});

const form = useForm({
    client_id: props.preselected_client_id || '',
    tipo_actividad_vulnerable: '',
    descripcion_actividad: '',
    monto_operacion: '',
    moneda: 'MXN',
    nombre_beneficiario: '',
    rfc_beneficiario: '',
    es_pep: false,
    fecha_operacion: new Date().toISOString().split('T')[0],
    observaciones: '',
});

const umasEquivalente = computed(() => {
    if (!form.monto_operacion || !props.uma_diario) return 0;
    return Math.round(form.monto_operacion / props.uma_diario);
});

const superaAviso = computed(() => umasEquivalente.value >= 645);
const superaReporte = computed(() => umasEquivalente.value >= 1500);

const submit = () => {
    form.post(route('expedientes.store'));
};
</script>

<template>
    <Head title="Nuevo Expediente KYC" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('expedientes.index')" class="text-gray-500 hover:text-gray-700">&larr; Volver</Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Nuevo Expediente KYC</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6 rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Datos de la Operación</h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Cliente</label>
                            <select v-model="form.client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Seleccionar cliente...</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.razon_social }} ({{ client.rfc }})
                                </option>
                            </select>
                            <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">{{ form.errors.client_id }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Actividad Vulnerable (Art. 17 LFPIORPI)</label>
                            <select v-model="form.tipo_actividad_vulnerable" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Seleccionar...</option>
                                <option v-for="(nombre, clave) in actividades_vulnerables" :key="clave" :value="clave">
                                    {{ clave }}. {{ nombre }}
                                </option>
                            </select>
                            <p v-if="form.errors.tipo_actividad_vulnerable" class="mt-1 text-sm text-red-600">{{ form.errors.tipo_actividad_vulnerable }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Monto de Operación</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                                <input v-model.number="form.monto_operacion" type="number" step="0.01" min="0"
                                    class="block w-full rounded-md border-gray-300 pl-7 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                            </div>
                            <p v-if="form.errors.monto_operacion" class="mt-1 text-sm text-red-600">{{ form.errors.monto_operacion }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Equivalente en UMAs</label>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="text-2xl font-bold" :class="superaReporte ? 'text-red-600' : superaAviso ? 'text-yellow-600' : 'text-green-600'">
                                    {{ umasEquivalente.toLocaleString() }}
                                </span>
                                <span class="text-sm text-gray-500">UMAs ({{ uma_diario }}/día)</span>
                            </div>
                            <div class="mt-1 space-y-1">
                                <p v-if="superaReporte" class="text-xs text-red-600 font-medium">Supera umbral de reporte UIF (1,500 UMAs)</p>
                                <p v-else-if="superaAviso" class="text-xs text-yellow-600 font-medium">Supera umbral de aviso (645 UMAs)</p>
                                <p v-else class="text-xs text-green-600">Dentro de umbrales normales</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fecha de Operación</label>
                            <input v-model="form.fecha_operacion" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Moneda</label>
                            <select v-model="form.moneda" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="MXN">MXN</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Beneficiario</h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre del Beneficiario</label>
                            <input v-model="form.nombre_beneficiario" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RFC Beneficiario</label>
                            <input v-model="form.rfc_beneficiario" type="text" maxlength="13" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 font-mono uppercase" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2">
                                <input v-model="form.es_pep" type="checkbox" class="rounded border-gray-300 text-orange-500 focus:ring-orange-500" />
                                <span class="text-sm text-gray-700">Persona Políticamente Expuesta (PEP)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción / Observaciones</label>
                        <textarea v-model="form.observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4 border-t pt-4">
                        <Link :href="route('expedientes.index')" class="text-sm text-gray-600 hover:text-gray-800">Cancelar</Link>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center rounded-md bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600 disabled:opacity-50">
                            {{ form.processing ? 'Creando...' : 'Crear Expediente' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
