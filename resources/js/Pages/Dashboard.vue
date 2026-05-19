<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Menangkap properti dokumen yang dikirim dari Backend (jika ada)
const props = defineProps({
    documents: {
        type: Array,
        default: () => []
    }
});

// Form menggunakan standar Inertia untuk membuat dokumen baru
const form = useForm({
    title: ''
});

const isModalOpen = ref(false);

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const createDocument = () => {
    form.post(route('documents.store'), {
        onSuccess: () => {
            closeModal();
        }
    });
};

const openEditor = (id) => {
    router.get(`/editor/${id}`);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard Dokumen
                </h2>
                <button 
                    @click="openModal" 
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg shadow transition duration-150 flex items-center gap-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Dokumen Baru
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <div v-if="documents.length === 0" class="text-center py-12 text-gray-500">
                        <p class="text-lg">Belum ada dokumen yang tersedia.</p>
                        <p class="text-sm text-gray-400 mt-1">Klik tombol di kanan atas untuk membuat dokumen pertama Anda.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="doc in documents" 
                            :key="doc.id" 
                            @click="openEditor(doc.id)"
                            class="p-5 border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-md cursor-pointer transition bg-gray-50"
                        >
                            <h3 class="font-semibold text-gray-900 text-lg truncate mb-2">{{ doc.title }}</h3>
                            <p class="text-sm text-gray-500 line-clamp-3 font-mono bg-white p-2 rounded border border-gray-100 h-16">
                                {{ doc.content || 'Kosong...' }}
                            </p>
                            <div class="mt-4 text-xs text-gray-400 text-right">
                                Terakhir diubah: {{ new Date(doc.updated_at).toLocaleDateString('id-ID') }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Buat Dokumen Baru</h3>
                
                <form @submit.prevent="createDocument">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Dokumen</label>
                        <input 
                            v-model="form.title" 
                            type="text" 
                            required
                            placeholder="Contoh: Tugas Kelompok Web 2"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button 
                            type="button" 
                            @click="closeModal" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 rounded-lg shadow transition"
                        >
                            {{ form.processing ? 'Memproses...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>