<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    expediente: Object,
    uma_diario: Number,
    estados: Object,
});

const form = useForm({
    estado: props.expediente.estado,
    observaciones: props.expediente.observaciones || '',
});

const updateEstado = () => {
    form.put(route('expedientes.update', props.expediente.id));
};

const riesgoColor = (nivel) => ({
    bajo: 'bg-green-100 text-green-800',
    medio: 'bg-yellow-100 text-yellow-800',
    alto: 'bg-red-100 text-red-800',
}[nivel] || 'bg-gray-100 text-gray-800');

const estadoColor = (est) => ({
    pendiente: 'bg-yellow-100 text-yellow-800',
    en_revision: 'bg-blue-100 text-blue-800',
    completo: 'bg-green-100 text-green-800',
    reportado_uif: 'bg-purple-100 text-purple-800',
}[est] || 'bg-gray-100 text-gray-800');

const formatDate = (d) => d ? new Date(d).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' }) : '—';
const formatMoney = (m) => m ? `$${Number(m).toLocaleString('es-MX', { minimumFractionDigits: 2 })}` : '—';
</script>

<template>
    <Head :title="`Expediente ${expediente.folio}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('expedientes.index')" class="text-gray-500 hover:text-gray-700">&larr;</Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 font-mono">{{ expediente.folio }}</h2>
                    <span :class="riesgoColor(expediente.nivel_riesgo)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                        {{ expediente.nivel_riesgo }}
                    </span>
                    <span :class="estadoColor(expediente.estado)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                        {{ estados[expediente.estado] || expediente.estado }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Alerta alto riesgo -->
                <div v-if="expediente.nivel_riesgo === 'alto'" class="rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="flex items-center gap-3">
                        <span class="text-red-600 text-lg">!</span>
                        <div>
                            <p class="font-medium text-red-800">Expediente de Alto Riesgo</p>
                            <p class="text-sm text-red-700">
                                {{ expediente.supera_umbral_reporte ? 'Supera umbral de reporte UIF (1,500 UMAs).' : '' }}
                                {{ expediente.es_pep ? 'Beneficiario es Persona Políticamente Expuesta (PEP).' : '' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Datos principales -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="rounded-lg bg-white shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Datos de la Operación</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Cliente</p>
                                    <Link v-if="expediente.client" :href="route('clients.show', expediente.client.id)" class="font-medium text-gray-900 hover:text-orange-600">
                                        {{ expediente.client.razon_social }}
                                    </Link>
                                    <p class="text-sm text-gray-500 font-mono">{{ expediente.client?.rfc }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Actividad Vulnerable</p>
                                    <p class="font-medium">{{ expediente.tipo_actividad_vulnerable }}</p>
                                    <p class="text-sm text-gray-500">{{ expediente.descripcion_actividad }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Monto</p>
                                    <p class="text-xl font-bold font-mono">{{ formatMoney(expediente.monto_operacion) }}</p>
                                    <p class="text-sm text-gray-500">{{ expediente.moneda }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Equivalente UMAs</p>
                                    <p class="text-xl font-bold font-mono" :class="expediente.supera_umbral_reporte ? 'text-red-600' : expediente.supera_umbral_aviso ? 'text-yellow-600' : 'text-green-600'">
                                        {{ expediente.umas_equivalente?.toLocaleString() }}
                                    </p>
                                    <p class="text-xs text-gray-500">UMA diario: ${{ uma_diario }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Fecha de Operación</p>
                                    <p>{{ formatDate(expediente.fecha_operacion) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Fecha Reporte UIF</p>
                                    <p>{{ formatDate(expediente.fecha_reporte_uif) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Beneficiario</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Nombre</p>
                                    <p>{{ expediente.nombre_beneficiario || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">RFC</p>
                                    <p class="font-mono">{{ expediente.rfc_beneficiario || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">PEP</p>
                                    <span v-if="expediente.es_pep" class="inline-flex items-center rounded-full bg-purple-100 text-purple-800 px-2.5 py-0.5 text-xs font-medium">Sí — PEP</span>
                                    <span v-else class="text-gray-500">No</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar: Estado y retención -->
                    <div class="space-y-6">
                        <div class="rounded-lg bg-white shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Actualizar Estado</h3>
                            <form @submit.prevent="updateEstado" class="space-y-4">
                                <div>
                                    <select v-model="form.estado" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                        <option v-for="(label, key) in estados" :key="key" :value="key">{{ label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <textarea v-model="form.observaciones" rows="3" placeholder="Observaciones..."
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"></textarea>
                                </div>
                                <button type="submit" :disabled="form.processing"
                                    class="w-full inline-flex justify-center items-center rounded-md bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600 disabled:opacity-50">
                                    Actualizar
                                </button>
                            </form>
                        </div>

                        <div class="rounded-lg bg-white shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Retención Legal</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500">Retención obligatoria</p>
                                    <p class="font-medium">10 años (Art. 18 LFPIORPI)</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Vencimiento retención</p>
                                    <p class="font-medium">{{ formatDate(expediente.fecha_vencimiento_retencion) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Umbrales</h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Aviso (645 UMAs)</span>
                                    <span :class="expediente.supera_umbral_aviso ? 'text-yellow-600 font-medium' : 'text-green-600'">
                                        {{ expediente.supera_umbral_aviso ? 'Superado' : 'OK' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Reporte UIF (1500 UMAs)</span>
                                    <span :class="expediente.supera_umbral_reporte ? 'text-red-600 font-medium' : 'text-green-600'">
                                        {{ expediente.supera_umbral_reporte ? 'Superado' : 'OK' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
