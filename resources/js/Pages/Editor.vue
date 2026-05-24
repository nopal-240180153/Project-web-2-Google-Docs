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

// KUNCI UTAMA: State untuk menyimpan koordinat kursor user lain
const activeCursors = ref({});

onMounted(() => {
    if (window.Echo) {
        const channel = Echo.join(`document.${props.document.id}`);

        channel.here((users) => {
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
                // Hapus kursor jika user tersebut keluar dari halaman editor
                delete activeCursors.value[user.id];
            })
            .listenForWhisper('typing', (e) => {
                typingUser.value = e.name;

                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    typingUser.value = '';
                }, 2000);
            })
            // FITUR BARU: Menerima bisikan koordinat kursor dari user lain
            .listenForWhisper('mouse-move', (e) => {
                activeCursors.value[e.userId] = {
                    name: e.userName,
                    x: e.x,
                    y: e.y,
                    color: e.color
                };
            })
            .listen('DocumentUpdated', (e) => {
                textContent.value = e.content; 
            });
    }
});

// Fungsi memicu bisikan mengetik saat menekan keyboard
const handleKeyDownWhisper = () => {
    if (window.Echo) {
        Echo.join(`document.${props.document.id}`)
            .whisper('typing', {
                id: currentUser.id,
                name: currentUser.name
            });
    }
};

// FITUR BARU: Fungsi untuk menangkap gerakan mouse kita lalu disiarkan ke kolaborator lain
const handleMouseMove = (event) => {
    if (window.Echo) {
        // Ambil warna kursor unik secara otomatis berdasarkan ID user agar tidak tertukar
        const colors = ['#EF4444', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'];
        const userColor = colors[currentUser.id % colors.length];

        Echo.join(`document.${props.document.id}`)
            .whisper('mouse-move', {
                userId: currentUser.id,
                userName: currentUser.name,
                x: event.clientX,
                y: event.clientY,
                color: userColor
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

        <div class="py-12" @mousemove="handleMouseMove">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 relative">
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

        <template v-for="(cursor, id) in activeCursors" :key="id">
            <div 
                class="fixed pointer-events-none z-50 transition-all duration-75 ease-out"
                :style="{ left: `${cursor.x}px`, top: `${cursor.y}px` }"
            >
                <svg 
                    class="h-5 w-5 drop-shadow-md" 
                    viewBox="0 0 24 24" 
                    :style="{ fill: cursor.color, stroke: '#ffffff', strokeWidth: '1.5px' }"
                >
                    <path d="M4.28 2.33a.5.5 0 0 0-.74.61l6.4 18.52a.5.5 0 0 0 .91-.12l2.36-7.38 7.38-2.36a.5.5 0 0 0 .12-.91L4.28 2.33Z" />
                </svg>

                <div 
                    class="ml-3 px-2 py-0.5 rounded text-xs font-semibold text-white whitespace-nowrap shadow-md"
                    :style="{ backgroundColor: cursor.color }"
                >
                    {{ cursor.name }}
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>