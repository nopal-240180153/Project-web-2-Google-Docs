<script setup>
import { onMounted, ref, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// Tangkap props dari Controller Laravel
const { document, isLoggedIn } = defineProps({
    document: Object,
    isLoggedIn: Boolean
});

const editorRef = ref(null);
let quill = null;
let saveTimeout = null;
const saveStatus = ref('');
const isSaving = ref(false);

// Fungsi menyalin link rute ke clipboard HP/Laptop orang lain
const copyShareLink = () => {
    // Gunakan route() helper untuk generate URL berdasarkan APP_URL
    const shareUrl = route('editor.show', document.id, true);
    navigator.clipboard.writeText(shareUrl);
    alert('Link kolaborasi berhasil disalin! Kirimkan ke temanmu:\n' + shareUrl);
};

// Fungsi untuk menyimpan konten dokumen ke database
const saveDocument = async () => {
    if (!isLoggedIn || !quill) return;

    isSaving.value = true;
    saveStatus.value = 'Menyimpan...';

    try {
        // Ambil CSRF token dari meta tag HTML
        const csrfToken = window.document.querySelector('meta[name="csrf-token"]')?.content;
        
        const response = await fetch(`/documents/${document.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                content: quill.root.innerHTML,
            }),
        });

        if (response.ok) {
            const data = await response.json();
            saveStatus.value = '✓ Tersimpan';
            isSaving.value = false;

            // Hapus pesan "Tersimpan" setelah 2 detik
            setTimeout(() => {
                saveStatus.value = '';
            }, 2000);
        } else {
            saveStatus.value = '✗ Gagal menyimpan';
            isSaving.value = false;
        }
    } catch (error) {
        console.error('Error saving document:', error);
        saveStatus.value = '✗ Error: ' + error.message;
        isSaving.value = false;
    }
};

// Fungsi debounce untuk menghindari save terlalu sering
const debounceAutoSave = () => {
    if (saveTimeout) {
        clearTimeout(saveTimeout);
    }

    saveTimeout = setTimeout(() => {
        saveDocument();
    }, 1500); // Save setiap 1.5 detik setelah user berhenti mengetik
};

onMounted(async () => {
    await nextTick();
    if (editorRef.value) {
        // Inisialisasi Quill Editor dengan pengaman login
        quill = new Quill(editorRef.value, {
            theme: 'snow',
            readOnly: !isLoggedIn, // JIKA BELUM LOGIN, EDITOR DIKUNCI (READ-ONLY)
            modules: {
                toolbar: isLoggedIn ? true : false // JIKA BELUM LOGIN, TOOLBAR DIHILANGKAN
            }
        });

        // Tampilkan isi teks dokumen yang tersimpan di database
        if (document.content) {
            quill.root.innerHTML = document.content;
        }

        // JALANKAN AUTO-SAVE HANYA JIKA USER SUDAH LOGIN
        if (isLoggedIn) {
            // Event listener untuk mendeteksi perubahan teks
            quill.on('text-change', () => {
                debounceAutoSave();
            });
        }
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-4xl mx-auto">
            
            <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ document.title }}</h1>
                    <p v-if="saveStatus" class="text-sm mt-2" :class="saveStatus.includes('✓') ? 'text-green-600' : 'text-red-600'">
                        {{ saveStatus }}
                    </p>
                </div>
                
                <div class="space-x-2">
                    <button @click="copyShareLink" class="bg-blue-600 text-white px-4 py-2 rounded-md font-medium shadow hover:bg-blue-700 transition">
                        Bagikan Link
                    </button>
                    <Link href="/dashboard" class="bg-gray-500 text-white px-4 py-2 rounded-md font-medium shadow hover:bg-gray-600 transition">
                        Kembali ke Dashboard
                    </Link>
                </div>
            </div>

            <div v-if="!isLoggedIn" class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 mb-6 rounded-r-lg shadow-sm flex justify-between items-center animate-pulse">
                <div>
                    <h3 class="font-bold text-lg flex items-center">
                        ⚠️ Mode Hanya Melihat (Read-Only)
                    </h3>
                    <p class="text-sm text-amber-700 mt-1">
                        Kamu tidak bisa mengedit dokumen ini. Silakan login terlebih dahulu untuk membuka akses keyboard dan mulai mengetik bersama.
                    </p>
                </div>
<Link :href="route('login') + `?redirect=${encodeURIComponent('/editor/' + document.id)}`" class="bg-amber-600 text-white px-5 py-2.5 rounded-md font-bold hover:bg-amber-700 transition shadow-md whitespace-nowrap">
                    Log In Sekarang
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div ref="editorRef" style="min-height: 450px; font-size: 16px; line-height: 1.6;"></div>
            </div>

        </div>
    </div>
</template>
