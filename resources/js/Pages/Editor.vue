<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    document: Object
});

const pageProps = usePage().props;
const currentUser = pageProps.auth?.user || {};

const textContent = ref(props.document?.content || '');
const activeUsers = ref([]);
const textareaRef = ref(null);

// Status simpan manual & Status mengetik user lain
const isSaving = ref(false);
const saveMessage = ref('');
const typingUser = ref(''); 
let typingTimeout = null;

onMounted(() => {
    if (window.Echo) {
        Echo.join(`document.${props.document.id}`)
            .here((users) => {
                activeUsers.value = users.filter(u => u.id !== currentUser.id);
            })
            .joining((user) => {
                if (!activeUsers.value.some(u => u.id === user.id)) {
                    activeUsers.value.push(user);
                }
            })
            .leaving((user) => {
                activeUsers.value = activeUsers.value.filter(u => u.id !== user.id);
                if (typingUser.value === user.name) {
                    typingUser.value = '';
                }
            })
            .listenForWhisper('typing', (e) => {
                // Menerima bisikan jika user lain sedang mengetik
                typingUser.value = e.name;

                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    typingUser.value = '';
                }, 2000);
            })
            // KUNCI UTAMA: Mendengarkan siaran teks baru yang disimpan oleh user lain
            .listen('DocumentUpdated', (e) => {
                // Update isi text editor secara instan tanpa perlu refresh!
                textContent.value = e.content; 
            });
    }
});

// Fungsi memicu bisikan nama kamu ke user lain saat kamu menekan keyboard
const handleKeyDownWhisper = () => {
    if (window.Echo) {
        Echo.join(`document.${props.document.id}`)
            .whisper('typing', {
                id: currentUser.id,
                name: currentUser.name
            });
    }
};

// Fungsi tombol SAVE Manual ke MySQL
const saveDocumentManual = () => {
    isSaving.value = true;
    saveMessage.value = '';

    router.put(`/editor/${props.document.id}`, {
        content: textContent.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSaving.value = false;
            saveMessage.value = '✓ Dokumen berhasil disimpan ke database!';
            
            setTimeout(() => {
                saveMessage.value = '';
            }, 3000);
        },
        onError: () => {
            isSaving.value = false;
            saveMessage.value = '❌ Gagal menyimpan dokumen!';
        }
    });
};

onUnmounted(() => {
    if (window.Echo) {
        Echo.leave(`document.${props.document.id}`);
    }
    clearTimeout(typingTimeout);
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Editor: {{ document.title }}
                    </h2>
                    
                    <span v-if="saveMessage" class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded border border-green-200">
                        {{ saveMessage }}
                    </span>
                </div>
                
                <div class="flex gap-4 items-center">
                    <div class="flex gap-2 items-center border-r pr-4 border-gray-200">
                        <span class="text-xs text-gray-500">Kolaborator:</span>
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full font-bold shadow-sm">
                            Anda ({{ currentUser.name }})
                        </span>
                        <span v-for="user in activeUsers" :key="user.id" class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full font-bold shadow-sm">
                            {{ user.name }}
                        </span>
                    </div>

                    <button 
                        @click="saveDocumentManual"
                        :disabled="isSaving"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium text-sm rounded-lg shadow transition flex items-center gap-2"
                    >
                        <span v-if="isSaving">Menyimpan...</span>
                        <span v-else>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="relative bg-white p-6 shadow-sm sm:rounded-lg border border-gray-200">
                    
                    <textarea
                        ref="textareaRef"
                        v-model="textContent"
                        @keydown="handleKeyDownWhisper"
                        class="w-full h-96 p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-base shadow-inner resize-none"
                        placeholder="Mulai ketik isi dokumen di sini..."
                    ></textarea>

                    <div class="mt-2 h-5 text-sm text-gray-500 italic flex items-center gap-2">
                        <span v-if="typingUser" class="flex items-center gap-1.5 text-blue-600 font-medium">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </span>
                            ✍️ {{ typingUser }} sedang mengetik...
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>