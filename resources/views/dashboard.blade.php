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

    <!-- Files List -->
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
                    <a 
                        href="{{ route('file.view', $file->id) }}"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors duration-200"
                    >
                        Lihat
                    </a>
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
        fileName: '',
        periodInput: '',
        uploading: false,
        modalError: '',
        
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
        }
    };
}
</script>
@endsection
