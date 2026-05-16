<template>
  <div class="editor-container">
    <div class="editor-header">
      <h2>DOCS Sharing</h2>
      <span class="status-text" :class="{ 'text-saving': isSaving }">
        {{ statusMessage }}
      </span>
    </div>

    <div class="editor-body">
      <div ref="editorElement"></div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import axios from 'axios';
import Quill from 'quill';
import 'quill/dist/quill.snow.css'; // Ambil style css toolbar bawaan quill

// Menangkap data lembar kerja lama dari database Laravel

const props = defineProps({
  document: Object
});

const editorElement = ref(null);
let quillInstance = null;
const isSaving = ref(false);
const statusMessage = ref('Semua perubahan tersimpan');
let saveTimeout = null;

onMounted(() => {
  // Inisialisasi Quill Editor dengan toolbar standar Google Docs
  quillInstance = new Quill(editorElement.value, {
    theme: 'snow',
    placeholder: 'Ketik di sini...',
    modules: {
      toolbar: [
        [{ 'header': [1, 2, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['clean']
      ]
    }
  });

  // Jika di database sudah ada isinya, tampilkan langsung di dalam editor
  if (props.document && props.document.content) {
    quillInstance.root.innerHTML = props.document.content;
  }

  // Logika Auto-Save otomatis 2 detik setelah user berhenti mengetik
  quillInstance.on('text-change', () => {
    statusMessage.value = 'Sedang mengetik...';
    isSaving.value = true;

    clearTimeout(saveTimeout);

    saveTimeout = setTimeout(() => {
      statusMessage.value = 'Menyimpan ke database...';
      const htmlContent = quillInstance.root.innerHTML;

      // Kirim data konten text editor ke route Laravel via Axios
      axios.post('/editor/save', { content: htmlContent })
        .then(response => {
          statusMessage.value = 'Semua perubahan tersimpan';
          isSaving.value = false;
        })
        .catch(error => {
          statusMessage.value = 'Gagal menyimpan data otomatis!';
          isSaving.value = false;
          console.error(error);
        });
    }, 2000);
  });
});

onBeforeUnmount(() => {
  clearTimeout(saveTimeout);
});
</script>

<style scoped>
.editor-container {
  max-width: 850px;
  margin: 40px auto;
  background: #fff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  font-family: 'Segoe UI', Roboto, sans-serif;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #f5f5f5;
  padding-bottom: 15px;
  margin-bottom: 25px;
}

.editor-header h2 {
  margin: 0;
  color: #2c3e50;
  font-size: 22px;
}

.status-text {
  font-size: 13px;
  color: #27ae60;
  font-weight: 600;
}

.text-saving {
  color: #e67e22;
}

/* Mengatur tinggi area putih tempat mengetik dokumen */
:deep(.ql-editor) {
  min-height: 400px;
  font-size: 16px;
  line-height: 1.6;
}
:deep(.ql-toolbar.ql-snow) {
  border-top-left-radius: 6px;
  border-top-right-radius: 6px;
  background: #fafafa;
}
:deep(.ql-container.ql-snow) {
  border-bottom-left-radius: 6px;
  border-bottom-right-radius: 6px;
}
</style>