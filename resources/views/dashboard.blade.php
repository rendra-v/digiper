@extends('layout')

@section('title', 'Dashboard')

@section('content')
<div x-data="dashboardApp()" class="min-h-screen bg-gradient-to-br from-neutral-50 via-neutral-50 to-blue-50 dark:from-neutral-950 dark:via-neutral-900 dark:to-neutral-900 pb-12">
    <!-- Header Section with Gradient -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 dark:from-blue-900 dark:via-blue-800 dark:to-cyan-900 pt-12 pb-16 mb-8 shadow-lg">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-72 h-72 bg-white rounded-full mix-blend-multiply filter blur-3xl"></div>
            <div class="absolute top-0 right-0 w-72 h-72 bg-cyan-300 rounded-full mix-blend-multiply filter blur-3xl animation-delay-2000"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-3 bg-white/20 rounded-xl">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-5xl font-bold text-white tracking-tight">Manajemen File</h2>
                        <p class="text-blue-100 mt-1">Kelola dan lihat file Excel Anda dengan mudah</p>
                    </div>
                </div>
                <div class="flex gap-4 mt-6">
                    <div class="flex items-center gap-2 text-blue-100">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H3a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1V6a1 1 0 00-1-1h-3a1 1 0 000-2 2 2 0 00-2 2v3h6V5a2 2 0 00-2-2H6a2 2 0 00-2 2v3a1 1 0 000 2H4V5z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ count($excelFiles) }} File</span>
                    </div>
                </div>
            </div>
            
            <button 
                @click="showUploadModal = true"
                class="group relative px-8 py-4 bg-white text-blue-600 rounded-xl font-semibold shadow-lg hover:shadow-2xl transition-all duration-300 hover:scale-105 flex items-center gap-3"
            >
                <svg class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah File Baru</span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            </button>
        </div>
    </div>

    <!-- Upload Modal -->
    <div 
        x-show="showUploadModal"
        @click.self="showUploadModal = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-cloak
        x-transition
    >
        <div class="bg-white dark:bg-neutral-900 rounded-2xl p-8 max-w-md w-full shadow-2xl border border-neutral-200 dark:border-neutral-800">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 dark:text-white">Upload File</h3>
                </div>
                <button 
                    @click="showUploadModal = false"
                    class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="uploadFile" class="space-y-6">
                <!-- File Input -->
                <div>
                    <label class="block text-sm font-semibold text-neutral-900 dark:text-white mb-3">File Excel</label>
                    <input 
                        type="file" 
                        @change="handleFileSelect($event)"
                        accept=".xlsx,.xls,.xlsm,.xlsb,.csv"
                        x-ref="fileInput"
                        class="hidden"
                        required
                    >
                    <button 
                        type="button"
                        @click="$refs.fileInput.click()"
                        class="w-full px-4 py-3 border-2 border-dashed border-neutral-300 dark:border-neutral-700 rounded-xl hover:border-blue-400 dark:hover:border-blue-600 transition-colors duration-200 text-left text-neutral-700 dark:text-neutral-300 hover:bg-blue-50 dark:hover:bg-blue-900/10 group"
                    >
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-neutral-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span x-text="fileName || 'Pilih atau drag file...'" class="font-medium"></span>
                        </div>
                        <p class="text-xs text-neutral-500 mt-1">Format: .xlsx, .xls, .csv (Max 100MB)</p>
                    </button>
                </div>

                <!-- Period Input -->
                <div>
                    <label class="block text-sm font-semibold text-neutral-900 dark:text-white mb-3">Periode</label>
                    <input 
                        type="text"
                        x-model="periodInput"
                        placeholder="Contoh: Desember 2025"
                        class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl dark:bg-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-ring"
                        required
                    >
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">Format: Bulan Tahun</p>
                </div>

                <!-- Error Message -->
                <div x-show="modalError" class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-lg" x-transition>
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-red-700 dark:text-red-400" x-text="modalError"></p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        @click="showUploadModal = false"
                        class="flex-1 px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 font-semibold transition-colors duration-200"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        :disabled="uploading || !fileName || !periodInput"
                        :class="uploading || !fileName || !periodInput ? 'opacity-50 cursor-not-allowed' : 'hover:from-blue-700 hover:to-cyan-700'"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2"
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
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-cloak
        x-transition
    >
        <div class="bg-white dark:bg-neutral-900 rounded-2xl p-8 max-w-md w-full shadow-2xl border border-neutral-200 dark:border-neutral-800">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 dark:text-white">Ubah Periode</h3>
                </div>
                <button 
                    @click="showRenameModal = false"
                    class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="renamePeriod" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-neutral-900 dark:text-white mb-3">Periode Baru</label>
                    <input 
                        type="text"
                        x-model="renameInput"
                        placeholder="Contoh: Desember 2025"
                        class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl dark:bg-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:focus:ring-amber-400 transition-ring"
                        required
                    >
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">Format: Bulan Tahun</p>
                </div>

                <!-- Error Message -->
                <div x-show="renameError" class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-lg" x-transition>
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-red-700 dark:text-red-400" x-text="renameError"></p>
                    </div>
                </div>

                <!-- Success Message -->
                <div x-show="renameSuccess" class="p-4 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900/50 rounded-lg" x-transition>
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-green-700 dark:text-green-400" x-text="renameSuccess"></p>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button 
                        type="button"
                        @click="showRenameModal = false"
                        class="flex-1 px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 font-semibold transition-colors duration-200"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        :disabled="renamingFile || !renameInput"
                        :class="renamingFile || !renameInput ? 'opacity-50 cursor-not-allowed' : 'hover:from-amber-700 hover:to-orange-700'"
                        class="flex-1 px-4 py-3 bg-gradient-to-r from-amber-600 to-orange-600 text-white rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2"
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
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        x-cloak
        x-transition
    >
        <div class="bg-white dark:bg-neutral-900 rounded-2xl p-8 max-w-md w-full shadow-2xl border border-neutral-200 dark:border-neutral-800">
            <div class="mb-8">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 dark:bg-red-900/30 rounded-2xl mb-6">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-center text-neutral-900 dark:text-white mb-2">Hapus File?</h3>
                <p class="text-center text-neutral-600 dark:text-neutral-400">
                    File <strong class="font-semibold" x-text="selectedFileName"></strong> akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <!-- Error Message -->
            <div x-show="deleteError" class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-lg mb-6" x-transition>
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-700 dark:text-red-400" x-text="deleteError"></p>
                </div>
            </div>

            <div class="flex gap-3">
                <button 
                    @click="showDeleteModal = false"
                    class="flex-1 px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800 font-semibold transition-colors duration-200"
                >
                    Batal
                </button>
                <button 
                    @click="deleteFile"
                    :disabled="deletingFile"
                    :class="deletingFile ? 'opacity-50 cursor-not-allowed' : 'hover:from-red-700 hover:to-pink-700'"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2"
                >
                    <span x-show="!deletingFile" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </span>
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

    <!-- Search and Filter Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input 
                type="text"
                x-model="searchQuery"
                placeholder="Cari file atau periode..."
                class="w-full pl-12 pr-4 py-3 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-neutral-900 dark:text-white placeholder-neutral-500 transition-all duration-200"
            >
        </div>
    </div>

    <!-- Files Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 auto-rows-max">
            @forelse($excelFiles as $file)
                <div 
                    x-show="!searchQuery || '{{ strtolower($file->original_filename) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($file->period) }}'.includes(searchQuery.toLowerCase())"
                    class="group bg-white dark:bg-neutral-900/80 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden hover:border-blue-400 dark:hover:border-blue-600 hover:shadow-2xl dark:hover:shadow-2xl transition-all duration-300 hover:scale-[1.03] hover:-translate-y-2 backdrop-blur-sm hover:backdrop-blur"
                >
                    <!-- Gradient overlay on hover -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/0 to-cyan-500/0 group-hover:from-blue-500/10 group-hover:to-cyan-500/10 transition-all duration-300 pointer-events-none"></div>
                    
                    <!-- Card Header with Status -->
                    <div class="relative px-5 pt-5 pb-4 border-b border-neutral-100 dark:border-neutral-800/50">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="p-2.5 bg-gradient-to-br from-green-100 to-green-50 dark:from-green-900/30 dark:to-green-800/20 rounded-lg group-hover:from-green-200 group-hover:to-green-100 dark:group-hover:from-green-800/50 dark:group-hover:to-green-700/40 transition-all duration-300 flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded-full text-xs font-semibold">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                Siap
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors break-words leading-tight">
                            {{ $file->original_filename }}
                        </h3>
                    </div>

                    <!-- Card Content -->
                    <div class="relative px-5 py-4 space-y-4">
                        <!-- Period Badge -->
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"/>
                            </svg>
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Periode</p>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $file->period }}</p>
                            </div>
                        </div>

                        <!-- Upload Date -->
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v2h16V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h12a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Diunggah</p>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $file->created_at->format('d M Y') }}</p>
                            </div>
                        </div>

                        <!-- Time -->
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00-.293.707l-2.828 2.829a1 1 0 101.415 1.415L9 10.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Waktu</p>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $file->created_at->format('H:i') }}</p>
                            </div>
                        </div>

                        <!-- File Type -->
                        <div class="pt-2 border-t border-neutral-100 dark:border-neutral-800/50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"/>
                            </svg>
                            <div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Format</p>
                                <p class="text-sm font-semibold text-neutral-900 dark:text-white">Microsoft Excel</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="relative px-5 py-4 border-t border-neutral-100 dark:border-neutral-800/50 bg-neutral-50/50 dark:bg-neutral-900/30 flex gap-2">
                        <a 
                            href="{{ route('file.view', $file->id) }}"
                            class="flex-1 px-3 py-2.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 rounded-lg hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:text-white transition-all duration-200 text-sm font-medium flex items-center justify-center gap-2"
                            title="Lihat file"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat
                        </a>
                        
                        <button
                            @click="showRenameModal = true; selectedFileId = {{ $file->id }}; renameInput = '{{ $file->period }}'"
                            class="flex-1 px-3 py-2.5 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-300 rounded-lg hover:bg-amber-600 hover:text-white dark:hover:bg-amber-600 dark:hover:text-white transition-all duration-200 text-sm font-medium flex items-center justify-center gap-2"
                            title="Ubah periode"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        
                        <button
                            @click="showDeleteModal = true; selectedFileId = {{ $file->id }}; selectedFileName = '{{ $file->original_filename }}'"
                            class="flex-1 px-3 py-2.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-300 rounded-lg hover:bg-red-600 hover:text-white dark:hover:bg-red-600 dark:hover:text-white transition-all duration-200 text-sm font-medium flex items-center justify-center gap-2"
                            title="Hapus file"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
            <!-- Empty State -->
            <div class="text-center py-24">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-2xl mb-6">
                    <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-neutral-900 dark:text-white mb-3">Belum Ada File</h3>
                <p class="text-neutral-600 dark:text-neutral-400 mb-8 max-w-md mx-auto">Mulai dengan menambahkan file Excel pertama Anda untuk mengelola data dengan lebih mudah</p>
                <button 
                    @click="showUploadModal = true"
                    class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
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
        searchQuery: '',
        
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
