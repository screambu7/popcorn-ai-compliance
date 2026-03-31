<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    clients: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const nivelRiesgo = ref(props.filters?.nivel_riesgo || '');

let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('clients.index'), { search: value, nivel_riesgo: nivelRiesgo.value }, { preserveState: true, replace: true });
    }, 300);
});

watch(nivelRiesgo, (value) => {
    router.get(route('clients.index'), { search: search.value, nivel_riesgo: value }, { preserveState: true, replace: true });
});

const riesgoColor = (nivel) => ({
    bajo: 'bg-green-100 text-green-800',
    medio: 'bg-yellow-100 text-yellow-800',
    alto: 'bg-red-100 text-red-800',
}[nivel] || 'bg-gray-100 text-gray-800');
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Clientes</h2>
                <Link :href="route('clients.create')" class="inline-flex items-center rounded-md bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                    Nuevo Cliente
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row">
                    <input v-model="search" type="text" placeholder="Buscar por razón social, RFC o email..."
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                    <select v-model="nivelRiesgo" class="rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Todos los niveles</option>
                        <option value="bajo">Bajo</option>
                        <option value="medio">Medio</option>
                        <option value="alto">Alto</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Razón Social</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">RFC</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Régimen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Riesgo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <Link :href="route('clients.show', client.id)" class="font-medium text-gray-900 hover:text-orange-600">
                                        {{ client.razon_social }}
                                    </Link>
                                    <div class="flex gap-1 mt-1">
                                        <span v-if="client.actividad_vulnerable" class="inline-flex items-center rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-xs">Vulnerable</span>
                                        <span v-if="client.es_pep" class="inline-flex items-center rounded-full bg-purple-100 text-purple-800 px-2 py-0.5 text-xs">PEP</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 font-mono">{{ client.rfc }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ client.regimen_fiscal }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="riesgoColor(client.nivel_riesgo)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        {{ client.nivel_riesgo }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="client.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                                        {{ client.active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <Link :href="route('clients.edit', client.id)" class="text-orange-600 hover:text-orange-800">Editar</Link>
                                </td>
                            </tr>
                            <tr v-if="!clients.data?.length">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No se encontraron clientes</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="clients.links?.length > 3" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <nav class="flex items-center justify-between">
                            <p class="text-sm text-gray-700">
                                Mostrando {{ clients.from }} a {{ clients.to }} de {{ clients.total }} resultados
                            </p>
                            <div class="flex gap-1">
                                <Link v-for="link in clients.links" :key="link.label"
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
