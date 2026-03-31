<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    actividades_vulnerables: Object,
});

const form = useForm({
    razon_social: '',
    rfc: '',
    email: '',
    telefono: '',
    regimen_fiscal: '',
    regimen_fiscal_descripcion: '',
    actividad_vulnerable: false,
    actividades_vulnerables: [],
    obligado_antilavado: false,
    es_pep: false,
    nivel_riesgo: 'bajo',
});

const submit = () => {
    form.post(route('clients.store'));
};

const regimenes = [
    { clave: '601', desc: 'General de Ley Personas Morales' },
    { clave: '603', desc: 'Personas Morales con Fines no Lucrativos' },
    { clave: '605', desc: 'Sueldos y Salarios' },
    { clave: '606', desc: 'Arrendamiento' },
    { clave: '607', desc: 'Enajenación de Bienes' },
    { clave: '608', desc: 'Demás ingresos' },
    { clave: '610', desc: 'Residentes en el Extranjero' },
    { clave: '612', desc: 'Personas Físicas con Actividades Empresariales y Profesionales' },
    { clave: '614', desc: 'Ingresos por Intereses' },
    { clave: '616', desc: 'Sin Obligaciones Fiscales' },
    { clave: '620', desc: 'Sociedades Cooperativas de Producción' },
    { clave: '621', desc: 'Incorporación Fiscal' },
    { clave: '622', desc: 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras' },
    { clave: '625', desc: 'Régimen Simplificado de Confianza (RESICO)' },
    { clave: '626', desc: 'RESICO Personas Morales' },
];

const selectRegimen = (reg) => {
    form.regimen_fiscal = reg.clave;
    form.regimen_fiscal_descripcion = reg.desc;
};
</script>

<template>
    <Head title="Nuevo Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('clients.index')" class="text-gray-500 hover:text-gray-700">&larr; Volver</Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Nuevo Cliente</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6 rounded-lg bg-white p-6 shadow">
                    <!-- Datos Básicos -->
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Datos del Cliente</h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                            <input v-model="form.razon_social" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                            <p v-if="form.errors.razon_social" class="mt-1 text-sm text-red-600">{{ form.errors.razon_social }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">RFC</label>
                            <input v-model="form.rfc" type="text" maxlength="13" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 font-mono uppercase" />
                            <p v-if="form.errors.rfc" class="mt-1 text-sm text-red-600">{{ form.errors.rfc }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                            <input v-model="form.telefono" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Régimen Fiscal</label>
                            <select @change="selectRegimen(regimenes.find(r => r.clave === $event.target.value))" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="">Seleccionar...</option>
                                <option v-for="reg in regimenes" :key="reg.clave" :value="reg.clave">
                                    {{ reg.clave }} — {{ reg.desc }}
                                </option>
                            </select>
                            <p v-if="form.errors.regimen_fiscal" class="mt-1 text-sm text-red-600">{{ form.errors.regimen_fiscal }}</p>
                        </div>
                    </div>

                    <!-- Compliance -->
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mt-8">Clasificación Compliance</h3>

                    <div class="space-y-4">
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2">
                                <input v-model="form.actividad_vulnerable" type="checkbox" class="rounded border-gray-300 text-orange-500 focus:ring-orange-500" />
                                <span class="text-sm text-gray-700">Actividad Vulnerable (Art. 17 LFPIORPI)</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input v-model="form.es_pep" type="checkbox" class="rounded border-gray-300 text-orange-500 focus:ring-orange-500" />
                                <span class="text-sm text-gray-700">Persona Políticamente Expuesta (PEP)</span>
                            </label>
                        </div>

                        <div v-if="form.actividad_vulnerable">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Actividades Vulnerables</label>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <label v-for="(nombre, clave) in actividades_vulnerables" :key="clave" class="flex items-start gap-2">
                                    <input v-model="form.actividades_vulnerables" :value="clave" type="checkbox" class="mt-0.5 rounded border-gray-300 text-orange-500 focus:ring-orange-500" />
                                    <span class="text-sm text-gray-700">{{ clave }}. {{ nombre }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nivel de Riesgo</label>
                            <select v-model="form.nivel_riesgo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="bajo">Bajo</option>
                                <option value="medio">Medio</option>
                                <option value="alto">Alto</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end gap-4 border-t pt-4">
                        <Link :href="route('clients.index')" class="text-sm text-gray-600 hover:text-gray-800">Cancelar</Link>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center rounded-md bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600 disabled:opacity-50">
                            {{ form.processing ? 'Guardando...' : 'Crear Cliente' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
