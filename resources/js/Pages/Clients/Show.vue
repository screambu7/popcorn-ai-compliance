<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    client: Object,
    audit_logs: Array,
});

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
    <Head :title="client.razon_social" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('clients.index')" class="text-gray-500 hover:text-gray-700">&larr;</Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ client.razon_social }}</h2>
                    <span :class="riesgoColor(client.nivel_riesgo)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                        {{ client.nivel_riesgo }}
                    </span>
                    <span v-if="client.es_pep" class="inline-flex items-center rounded-full bg-purple-100 text-purple-800 px-2.5 py-0.5 text-xs font-medium">PEP</span>
                </div>
                <Link :href="route('clients.edit', client.id)" class="inline-flex items-center rounded-md bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                    Editar
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Info Card -->
                <div class="rounded-lg bg-white shadow">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div>
                                <p class="text-sm text-gray-500">RFC</p>
                                <p class="font-mono text-lg">{{ client.rfc }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p>{{ client.email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Teléfono</p>
                                <p>{{ client.telefono || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Régimen Fiscal</p>
                                <p>{{ client.regimen_fiscal }} {{ client.regimen_fiscal_descripcion ? `— ${client.regimen_fiscal_descripcion}` : '' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Actividad Vulnerable</p>
                                <p>{{ client.actividad_vulnerable ? 'Sí' : 'No' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Estado</p>
                                <span :class="client.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                    {{ client.active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Expedientes -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Expedientes KYC</h3>
                            <Link :href="route('expedientes.create', { client_id: client.id })" class="text-sm text-orange-600 hover:text-orange-800">
                                Nuevo Expediente
                            </Link>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div v-for="exp in client.expedientes" :key="exp.id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <Link :href="route('expedientes.show', exp.id)" class="font-medium text-gray-900 hover:text-orange-600">
                                        {{ exp.folio }}
                                    </Link>
                                    <p class="text-sm text-gray-500">${{ Number(exp.monto_operacion).toLocaleString() }} MXN — {{ exp.umas_equivalente }} UMAs</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="riesgoColor(exp.nivel_riesgo)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">{{ exp.nivel_riesgo }}</span>
                                    <span :class="estadoColor(exp.estado)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">{{ exp.estado }}</span>
                                </div>
                            </div>
                            <div v-if="!client.expedientes?.length" class="px-6 py-8 text-center text-gray-500">Sin expedientes</div>
                        </div>
                    </div>

                    <!-- Audit Log -->
                    <div class="rounded-lg bg-white shadow">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-medium text-gray-900">Historial de Cambios</h3>
                        </div>
                        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            <div v-for="log in audit_logs" :key="log.id" class="px-6 py-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">{{ log.accion }}</span>
                                    <span class="text-xs text-gray-500">{{ new Date(log.created_at).toLocaleString('es-MX') }}</span>
                                </div>
                                <p class="text-xs text-gray-500">Canal: {{ log.canal }} {{ log.notas ? `— ${log.notas}` : '' }}</p>
                            </div>
                            <div v-if="!audit_logs?.length" class="px-6 py-8 text-center text-gray-500">Sin historial</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
