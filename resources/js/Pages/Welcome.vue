<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

// Taktik Jitu: Amankan pembacaan data auth dari sisi script agar template tidak crash
const page = usePage();
const hasUser = computed(() => {
    return !!page.props.auth?.user;
});
</script>

<template>
    <Head title="Selamat Datang" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="relative min-h-screen flex flex-col items-center justify-center">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                
                <header class="flex justify-end py-10">
                    <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
                        
                        <Link
                            v-if="hasUser"
                            :href="route('dashboard')"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none dark:text-white dark:hover:text-white/80"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none dark:text-white dark:hover:text-white/80"
                            >
                                Log in
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none dark:text-white dark:hover:text-white/80"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                </header>

                <main class="mt-6 flex flex-col items-center justify-center text-center py-20">
                    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">
                        Project Mata Kuliah Pemograman Web 2 
                    </h1>
                    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-xl">
                        Buat dokumen, bagikan link ke rekan tim, dan ketik bersama secara instan.
                    </p>
                    <div class="mt-8">
                        <Link
                            :href="hasUser ? route('dashboard') : route('login')"
                            class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold shadow hover:bg-blue-700 transition"
                        >
                            Mulai Buat Dokumen
                        </Link>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black/40 dark:text-white/40">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>