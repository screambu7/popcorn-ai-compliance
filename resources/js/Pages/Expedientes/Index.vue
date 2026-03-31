<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    expedientes: Object,
    filters: Object,
    estados: Object,
});

const search = ref(props.filters?.search || '');
const estado = ref(props.filters?.estado || '');
const nivelRiesgo = ref(props.filters?.nivel_riesgo || '');

let timeout = null;
const applyFilters = () => {
    router.get(route('expedientes.index'), {
        search: search.value, estado: estado.value, nivel_riesgo: nivelRiesgo.value,
    }, { preserveState: true, replace: true });
};

watch(search, () => { clearTimeout(timeout); timeout = setTimeout(applyFilters, 300); });
watch([estado, nivelRiesgo], applyFilters);

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
</script>

<template>
    <Head title="Expedientes KYC" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Expedientes Antilavado</h2>
                <Link :href="route('expedientes.create')" class="inline-flex items-center rounded-md bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                    Nuevo Expediente
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row">
                    <input v-model="search" type="text" placeholder="Buscar por folio, beneficiario, RFC..."
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                    <select v-model="estado" class="rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Todos los estados</option>
                        <option v-for="(label, key) in estados" :key="key" :value="key">{{ label }}</option>
                    </select>
                    <select v-model="nivelRiesgo" class="rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Todos los niveles</option>
                        <option value="bajo">Bajo</option>
                        <option value="medio">Medio</option>
                        <option value="alto">Alto</option>
                    </select>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Folio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actividad</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Monto</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">UMAs</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Riesgo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="exp in expedientes.data" :key="exp.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Link :href="route('expedientes.show', exp.id)" class="font-medium text-gray-900 hover:text-orange-600 font-mono">
                                        {{ exp.folio }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    <Link v-if="exp.client" :href="route('clients.show', exp.client.id)" class="hover:text-orange-600">
                                        {{ exp.client.razon_social }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ exp.tipo_actividad_vulnerable }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900 font-mono">
                                    ${{ Number(exp.monto_operacion).toLocaleString() }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <span class="font-mono">{{ exp.umas_equivalente }}</span>
                                    <span v-if="exp.supera_umbral_reporte" class="ml-1 text-red-500" title="Supera umbral reporte UIF">!</span>
                                    <span v-else-if="exp.supera_umbral_aviso" class="ml-1 text-yellow-500" title="Supera umbral aviso">!</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="riesgoColor(exp.nivel_riesgo)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        {{ exp.nivel_riesgo }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="estadoColor(exp.estado)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        {{ exp.estado }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!expedientes.data?.length">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No se encontraron expedientes</td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="expedientes.links?.length > 3" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <nav class="flex items-center justify-between">
                            <p class="text-sm text-gray-700">
                                Mostrando {{ expedientes.from }} a {{ expedientes.to }} de {{ expedientes.total }}
                            </p>
                            <div class="flex gap-1">
                                <Link v-for="link in expedientes.links" :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[link.active ? 'bg-orange-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
                                    class="relative inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium"
                                    v-html="link.label" />
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
