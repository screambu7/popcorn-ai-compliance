<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ documentos: Object, filters: Object, clients: Array });
const filter = ref(props.filters?.filter || '');
watch(filter, (v) => router.get(route('documentos.index'), { filter: v }, { preserveState: true, replace: true }));

const uploadForm = useForm({ client_id: '', tipo: 'ine_frente', archivo: null, fecha_emision: '', fecha_vencimiento: '', notas: '' });
const showUpload = ref(false);
const handleFile = (e) => { uploadForm.archivo = e.target.files[0]; };
const upload = () => uploadForm.post(route('documentos.store'), { forceFormData: true, onSuccess: () => { showUpload.value = false; uploadForm.reset(); } });

const tipoLabels = { ine_frente: 'INE Frente', ine_reverso: 'INE Reverso', pasaporte: 'Pasaporte', curp: 'CURP', comprobante_domicilio: 'Comp. Domicilio', constancia_fiscal: 'Constancia Fiscal', acta_constitutiva: 'Acta Constitutiva', poder_notarial: 'Poder Notarial', comprobante_ingresos: 'Comp. Ingresos', otro: 'Otro' };

const isExpired = (d) => d && new Date(d) < new Date();
const isExpiringSoon = (d) => { if (!d) return false; const diff = (new Date(d) - new Date()) / (1000*60*60*24); return diff > 0 && diff <= 30; };
</script>

<template>
    <Head title="Documentos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-xl font-bold text-slate-900">Documentos</h1>
                <button @click="showUpload = !showUpload" class="inline-flex items-center gap-2 rounded-lg bg-popcorn-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-popcorn-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                    Subir Documento
                </button>
            </div>
        </template>

        <!-- Upload form -->
        <div v-if="showUpload" class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Subir Documento</h3>
            <form @submit.prevent="upload" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-slate-500">Cliente</label>
                    <select v-model="uploadForm.client_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Seleccionar...</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.razon_social }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500">Tipo</label>
                    <select v-model="uploadForm.tipo" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                        <option v-for="(label, key) in tipoLabels" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500">Archivo</label>
                    <input type="file" @change="handleFile" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-popcorn-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-popcorn-700 hover:file:bg-popcorn-100" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500">Fecha Emisión</label>
                    <input v-model="uploadForm.fecha_emision" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500">Fecha Vencimiento</label>
                    <input v-model="uploadForm.fecha_vencimiento" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm" />
                </div>
                <div class="flex items-end">
                    <button type="submit" :disabled="uploadForm.processing" class="rounded-lg bg-popcorn-500 px-6 py-2 text-sm font-semibold text-white hover:bg-popcorn-600 disabled:opacity-50">
                        {{ uploadForm.processing ? 'Subiendo...' : 'Subir' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mb-6">
            <select v-model="filter" class="rounded-lg border-slate-300 text-sm">
                <option value="">Todos</option>
                <option value="vencidos">Vencidos</option>
                <option value="por_vencer">Por Vencer (30 días)</option>
                <option value="sin_verificar">Sin Verificar</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200/60">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Archivo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Vencimiento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Verificado</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="doc in documentos.data" :key="doc.id" class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <Link v-if="doc.client" :href="route('clients.show', doc.client.id)" class="text-sm font-medium text-slate-900 hover:text-popcorn-600">{{ doc.client.razon_social }}</Link>
                        </td>
                        <td class="px-4 py-3"><span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ tipoLabels[doc.tipo] || doc.tipo }}</span></td>
                        <td class="px-4 py-3 text-sm text-slate-600">
                            <a :href="route('documentos.download', doc.id)" class="text-popcorn-600 hover:text-popcorn-500 underline">{{ doc.nombre_archivo }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="isExpired(doc.fecha_vencimiento)" class="inline-flex items-center rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-xs font-medium">Vencido</span>
                            <span v-else-if="isExpiringSoon(doc.fecha_vencimiento)" class="inline-flex items-center rounded-full bg-yellow-100 text-yellow-700 px-2 py-0.5 text-xs font-medium">{{ new Date(doc.fecha_vencimiento).toLocaleDateString('es-MX') }}</span>
                            <span v-else-if="doc.fecha_vencimiento" class="text-sm text-slate-500">{{ new Date(doc.fecha_vencimiento).toLocaleDateString('es-MX') }}</span>
                            <span v-else class="text-xs text-slate-400">—</span>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="doc.verificado" class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Verificado</span>
                            <span v-else class="text-xs text-slate-400">Pendiente</span>
                        </td>
                    </tr>
                    <tr v-if="!documentos.data?.length"><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-400">Sin documentos</td></tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
