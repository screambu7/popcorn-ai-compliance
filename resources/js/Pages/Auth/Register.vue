<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear Cuenta" />

        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Crear Cuenta</h2>
            <p class="mt-2 text-sm text-slate-500">
                Empieza a gestionar tu compliance hoy
            </p>
        </div>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Nombre completo</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <input id="name" type="text" v-model="form.name" required autofocus autocomplete="name"
                        class="block w-full rounded-lg border-slate-300 pl-10 text-sm shadow-sm transition focus:border-popcorn-500 focus:ring-popcorn-500"
                        placeholder="Tu nombre" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Correo electrónico</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <input id="email" type="email" v-model="form.email" required autocomplete="username"
                        class="block w-full rounded-lg border-slate-300 pl-10 text-sm shadow-sm transition focus:border-popcorn-500 focus:ring-popcorn-500"
                        placeholder="tu@empresa.mx" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </div>
                    <input id="password" type="password" v-model="form.password" required autocomplete="new-password"
                        class="block w-full rounded-lg border-slate-300 pl-10 text-sm shadow-sm transition focus:border-popcorn-500 focus:ring-popcorn-500"
                        placeholder="Mínimo 8 caracteres" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirmar contraseña</label>
                <div class="relative mt-1.5">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password"
                        class="block w-full rounded-lg border-slate-300 pl-10 text-sm shadow-sm transition focus:border-popcorn-500 focus:ring-popcorn-500"
                        placeholder="Repite tu contraseña" />
                </div>
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <button type="submit" :disabled="form.processing"
                class="flex w-full justify-center rounded-lg bg-popcorn-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-popcorn-500/25 transition-all hover:bg-popcorn-600 disabled:opacity-50">
                {{ form.processing ? 'Creando cuenta...' : 'Crear Cuenta' }}
            </button>

            <p class="text-center text-sm text-slate-500">
                ¿Ya tienes cuenta?
                <Link :href="route('login')" class="font-semibold text-popcorn-600 hover:text-popcorn-500">
                    Inicia sesión
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
