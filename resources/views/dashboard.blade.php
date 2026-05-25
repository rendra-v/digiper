@extends('layout')

@section('title', 'Dashboard')

@section('content')
<div x-data="dashboardApp()" class="min-h-screen">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-4xl font-semibold tracking-tight mb-3">Manajemen File Excel</h2>
                <p class="text-neutral-500 dark:text-neutral-400">Kelola dan lihat file Excel Anda</p>
            </div>
            <button 
                @click="showUploadModal = true"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200 shadow-sm hover:shadow-md"
            >
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah File
                </span>
            </button>
        </div>
    </div>

    <!-- Upload Modal -->
    <div 
        x-show="showUploadModal"
        @click.self="showUploadModal = false"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-cloak
    >
        <div class="bg-white dark:bg-neutral-900 rounded-lg p-8 max-w-md w-full mx-4 shadow-xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold">Upload File Excel</h3>
                <button 
                    @click="showUploadModal = false"
                    class="text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="uploadFile" class="space-y-6">
                <!-- File Input -->
                <div>
                    <label class="block text-sm font-medium mb-2">File Excel</label>
                    <input 
                        type="file" 
                        @change="handleFileSelect($event)"
                        accept=".xlsx,.xls,.csv"
                        x-ref="fileInput"
                        class="hidden"
                        required
                    >
                    <button 
                        type="button"
                        @click="$refs.fileInput.click()"
                        class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors duration-200 text-left text-neutral-700 dark:text-neutral-300"
                    >
                        <span x-text="fileName || 'Pilih File...'"></span>
                    </button>
                </div>

                <!-- Period Input -->
                <div>
                    <label class="block text-sm font-medium mb-2">Periode</label>
                    <input 
                        type="text"
                        x-model="periodInput"
                        placeholder="Contoh: Desember 2025"
                        class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg dark:bg-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Format: Bulan Tahun</p>
                </div>

                <!-- Error Message -->
                <div x-show="modalError" class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
                    <p class="text-sm text-red-700 dark:text-red-400" x-text="modalError"></p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        @click="showUploadModal = false"
                        class="flex-1 px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium transition-colors duration-200"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        :disabled="uploading || !fileName || !periodInput"
                        :class="uploading || !fileName || !periodInput ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium transition-colors duration-200 flex items-center justify-center gap-2"
                    >
                        <span x-show="!uploading">Upload</span>
                        <span x-show="uploading" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Uploading...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rename Period Modal -->
    <div 
        x-show="showRenameModal"
        @click.self="showRenameModal = false"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-cloak
    >
        <div class="bg-white dark:bg-neutral-900 rounded-lg p-8 max-w-md w-full mx-4 shadow-xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-semibold">Ubah Periode</h3>
                <button 
                    @click="showRenameModal = false"
                    class="text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="renamePeriod" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Periode Baru</label>
                    <input 
                        type="text"
                        x-model="renameInput"
                        placeholder="Contoh: Desember 2025"
                        class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg dark:bg-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-amber-500"
                        required
                    >
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Format: Bulan Tahun</p>
                </div>

                <!-- Error Message -->
                <div x-show="renameError" class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
                    <p class="text-sm text-red-700 dark:text-red-400" x-text="renameError"></p>
                </div>

                <!-- Success Message -->
                <div x-show="renameSuccess" class="p-3 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900/50 rounded-lg">
                    <p class="text-sm text-green-700 dark:text-green-400" x-text="renameSuccess"></p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        @click="showRenameModal = false"
                        class="flex-1 px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium transition-colors duration-200"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        :disabled="renamingFile || !renameInput"
                        :class="renamingFile || !renameInput ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-700'"
                        class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg font-medium transition-colors duration-200 flex items-center justify-center gap-2"
                    >
                        <span x-show="!renamingFile">Ubah</span>
                        <span x-show="renamingFile" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Updating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div 
        x-show="showDeleteModal"
        @click.self="showDeleteModal = false"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        x-cloak
    >
        <div class="bg-white dark:bg-neutral-900 rounded-lg p-8 max-w-md w-full mx-4 shadow-xl">
            <div class="mb-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-950/30 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-center mb-2">Hapus File?</h3>
                <p class="text-center text-neutral-600 dark:text-neutral-400">
                    File <strong x-text="selectedFileName"></strong> akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <!-- Error Message -->
            <div x-show="deleteError" class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg mb-4">
                <p class="text-sm text-red-700 dark:text-red-400" x-text="deleteError"></p>
            </div>

            <div class="flex gap-3">
                <button 
                    @click="showDeleteModal = false"
                    class="flex-1 px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium transition-colors duration-200"
                >
                    Batal
                </button>
                <button 
                    @click="deleteFile"
                    :disabled="deletingFile"
                    :class="deletingFile ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-700'"
                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg font-medium transition-colors duration-200 flex items-center justify-center gap-2"
                >
                    <span x-show="!deletingFile">Hapus</span>
                    <span x-show="deletingFile" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Deleting...
                    </span>
                </button>
            </div>
        </div>
    </div>

        <div class="grid gap-6">
        @forelse($excelFiles as $file)
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ $file->original_filename }}</h3>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-neutral-500 dark:text-neutral-400 ml-8">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $file->period }}
                            </span>
                            <span class="text-xs">{{ $file->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a 
                            href="{{ route('file.view', $file->id) }}"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors duration-200"
                        >
                            Lihat
                        </a>
                        <button
                            @click="showRenameModal = true; selectedFileId = {{ $file->id }}; renameInput = '{{ $file->period }}'"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-medium transition-colors duration-200 flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Ubah Periode
                        </button>
                        <button
                            @click="showDeleteModal = true; selectedFileId = {{ $file->id }}; selectedFileName = '{{ $file->original_filename }}'"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors duration-200 flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg p-12 text-center">
                <svg class="w-12 h-12 text-neutral-400 dark:text-neutral-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">Belum ada file</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">Mulai dengan menambahkan file Excel pertama Anda</p>
                <button 
                    @click="showUploadModal = true"
                    class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah File Pertama
                </button>
            </div>
        @endforelse
    </div>
</div>

<script>
function dashboardApp() {
    return {
        showUploadModal: false,
        showRenameModal: false,
        showDeleteModal: false,
        fileName: '',
        periodInput: '',
        uploading: false,
        modalError: '',
        renamingFile: false,
        renameInput: '',
        renameError: '',
        renameSuccess: '',
        deletingFile: false,
        deleteError: '',
        selectedFileId: null,
        selectedFileName: '',
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.fileName = file.name;
                this.modalError = '';
            }
        },

        async uploadFile() {
            if (!this.$refs.fileInput.files[0] || !this.periodInput) {
                this.modalError = 'Pilih file dan periode terlebih dahulu';
                return;
            }

            this.uploading = true;
            this.modalError = '';

            const formData = new FormData();
            formData.append('file', this.$refs.fileInput.files[0]);
            formData.append('period', this.periodInput);

            try {
                const response = await fetch('/upload-with-period', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    this.showUploadModal = false;
                    this.fileName = '';
                    this.periodInput = '';
                    this.$refs.fileInput.value = '';
                    
                    // Reload page to show new file
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    this.modalError = data.message || 'Upload gagal';
                }
            } catch (error) {
                this.modalError = 'Terjadi kesalahan: ' + error.message;
            } finally {
                this.uploading = false;
            }
        },

        async renamePeriod() {
            if (!this.renameInput.trim()) {
                this.renameError = 'Periode tidak boleh kosong';
                return;
            }

            this.renamingFile = true;
            this.renameError = '';
            this.renameSuccess = '';

            try {
                const response = await fetch(`/file/${this.selectedFileId}/rename-period`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ period: this.renameInput }),
                });

                const data = await response.json();

                if (data.success) {
                    this.renameSuccess = 'Periode berhasil diubah!';
                    setTimeout(() => {
                        this.showRenameModal = false;
                        window.location.reload();
                    }, 1000);
                } else {
                    this.renameError = data.message || 'Gagal mengubah periode';
                }
            } catch (error) {
                this.renameError = 'Terjadi kesalahan: ' + error.message;
            } finally {
                this.renamingFile = false;
            }
        },

        async deleteFile() {
            this.deletingFile = true;
            this.deleteError = '';

            try {
                const response = await fetch(`/file/${this.selectedFileId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();

                if (data.success) {
                    this.showDeleteModal = false;
                    // Reload page to show updated file list
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    this.deleteError = data.message || 'Gagal menghapus file';
                }
            } catch (error) {
                this.deleteError = 'Terjadi kesalahan: ' + error.message;
            } finally {
                this.deletingFile = false;
            }
        }
    };
}
</script>
@endsection
