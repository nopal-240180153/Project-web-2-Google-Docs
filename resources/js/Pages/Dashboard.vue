<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Menangkap data documents yang dikirim dari DocumentController index()
defineProps({
    documents: {
        type: Array,
        default: () => [],
    },
});

const showModal = ref(false);

const form = useForm({
    title: '',
});

// Fungsi untuk mengeksekusi pembuatan dokumen baru
const createDocument = () => {
    form.post(route('documents.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Dokumen</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Semua Dokumen Anda</h3>
                    <button 
                        @click="showModal = true" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold shadow hover:bg-blue-700 transition"
                    >
                        + Tambah File Baru
                    </button>
                </div>

                <div v-if="documents.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    Belum ada dokumen yang dibuat. Klik tombol di kanan atas untuk membuat dokumen pertama Anda!
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div 
                        v-for="document in documents" 
                        :key="document.id" 
                        class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 flex flex-col justify-between p-6 hover:shadow-md transition"
                    >
                        <div>
                            <div class="flex items-center space-x-3 mb-2">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
</svg>
                                <h4 class="text-xl font-bold text-gray-800 truncate max-w-[200px]">
                                    {{ document.title }}
                                </h4>
                            </div>
                            <p class="text-xs text-gray-400">
                                Dibuat pada: {{ new Date(document.created_at).toLocaleDateString('id-ID') }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <Link 
                                :href="route('editor.show', document.id)" 
                                class="w-full text-center block bg-gray-50 hover:bg-blue-50 text-blue-600 font-semibold py-2 px-4 rounded border border-blue-200 hover:border-blue-300 transition"
                            >
                                Buka Editor
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl transform transition-all">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Buat Dokumen Baru</h3>
                
                <form @submit.prevent="createDocument">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Dokumen</label>
                        <input 
                            v-model="form.title" 
                            type="text" 
                            class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" 
                            placeholder="Contoh: Laporan Skripsi, Notulen Rapat" 
                            required
                        />
                        <span v-if="form.errors.title" class="text-red-500 text-xs mt-1 block">{{ form.errors.title }}</span>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button 
                            type="button" 
                            @click="showModal = false" 
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md shadow transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Membuat...' : 'Buat File' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>