@extends('layout')

@section('title', 'Dashboard')

@section('content')
<div x-data="dashboardApp()" class="min-h-screen">
    <!-- Header Section -->
    <div class="mb-12">
        <h2 class="text-4xl font-semibold tracking-tight mb-3">Data Management</h2>
        <p class="text-neutral-500 dark:text-neutral-400">Unggah dan kelola file Excel Anda dengan mudah</p>
    </div>

    <!-- Upload Section -->
    <div class="mb-12">
        <div 
            @dragover="dragActive = true" 
            @dragleave="dragActive = false"
            @drop="dragActive = false; handleDrop($event)"
            class="relative border-2 border-dashed rounded-lg transition-all duration-200"
            :class="dragActive ? 'border-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-950/20' : 'border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900/50'"
        >
            <div class="px-12 py-16 flex flex-col items-center justify-center">
                <svg class="w-12 h-12 text-neutral-400 dark:text-neutral-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16v-4m0 0V8m0 4h4m-4 0H8M4 12a8 8 0 1116 0 8 8 0 01-16 0z"/>
                </svg>
                <p class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">Unggah file Excel</p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">Format: .xlsx, .xls, .csv</p>
                
                <input 
                    type="file" 
                    @change="handleFileSelect($event)"
                    accept=".xlsx,.xls,.csv"
                    class="hidden"
                    x-ref="fileInput"
                    id="fileInput"
                >
                
                <button 
                    @click="$refs.fileInput.click()"
                    class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200 shadow-sm hover:shadow-md"
                >
                    Pilih File
                </button>

                <p x-show="fileName" class="text-sm text-green-600 dark:text-green-400 mt-4 font-medium">
                    ✓ <span x-text="fileName"></span>
                </p>
            </div>

            <!-- Upload Status -->
            <div x-show="uploading" class="absolute inset-0 bg-white/80 dark:bg-neutral-950/80 flex items-center justify-center rounded-lg backdrop-blur-sm">
                <div class="flex flex-col items-center">
                    <div class="animate-spin mb-3">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">Mengunggah...</p>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div x-show="error" class="mt-4 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 rounded-lg">
            <p class="text-sm text-red-700 dark:text-red-400" x-text="error"></p>
        </div>

        <!-- Success Message -->
        <div x-show="success" class="mt-4 p-4 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900/50 rounded-lg">
            <p class="text-sm text-green-700 dark:text-green-400" x-text="success"></p>
        </div>
    </div>

    <!-- Table Section -->
    <div x-show="data.length > 0" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-lg overflow-hidden">
        <!-- Sheet Tabs & Table Header -->
        <div class="border-b border-neutral-200 dark:border-neutral-800">
            <!-- Sheet Tabs -->
            <div x-show="sheets.length > 0" class="px-8 pt-6 pb-0 flex gap-2 overflow-x-auto border-b border-neutral-200 dark:border-neutral-800">
                <template x-for="(sheet, idx) in sheets" :key="idx">
                    <button
                        @click="activeSheet = sheet"
                        :class="activeSheet === sheet ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400' : 'border-b-2 border-transparent text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-200'"
                        class="px-4 py-4 text-sm font-medium transition-colors duration-200"
                        x-text="sheet"
                    ></button>
                </template>
            </div>

            <!-- Table Header -->
            <div class="px-8 py-6 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold">Data Preview</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1"><span x-text="data.length"></span> baris data</p>
                </div>
                <div class="flex gap-2">
                    <button 
                        x-show="sheets.length > 0 && activeSheet !== sheets[0]"
                        @click="viewSheet(activeSheet)"
                        class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
                    >
                        Cek
                    </button>
                    <button 
                        @click="clearData()"
                        class="px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition-colors duration-200"
                    >
                        Hapus Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                    <tr class="border-b border-neutral-200 dark:border-neutral-800">
                        <th class="px-8 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wide">No</th>
                        <template x-for="(col, idx) in columns" :key="idx">
                            <th class="px-8 py-4 text-left text-xs font-semibold text-neutral-600 dark:text-neutral-400 uppercase tracking-wide" x-text="col"></th>
                        </template>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, idx) in data" :key="idx">
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors duration-150">
                            <td class="px-8 py-4 text-sm text-neutral-600 dark:text-neutral-400 font-medium" x-text="idx + 1"></td>
                            <template x-for="(col, cidx) in columns" :key="cidx">
                                <td class="px-8 py-4 text-sm text-neutral-900 dark:text-neutral-100">
                                    <span x-text="row[col] || '-'"></span>
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty State -->
    <div x-show="data.length === 0" class="text-center py-24">
        <svg class="w-16 h-16 text-neutral-300 dark:text-neutral-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-neutral-500 dark:text-neutral-400 text-lg font-medium">Belum ada data</p>
        <p class="text-neutral-400 dark:text-neutral-500 text-sm mt-2">Unggah file Excel untuk memulai</p>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function dashboardApp() {
        return {
            data: [],
            columns: [],
            fileName: '',
            uploading: false,
            error: '',
            success: '',
            dragActive: false,
            sheets: [],
            activeSheet: '',

            init() {
                // Check if there's existing data in session
                @if(!empty($perkaras))
                    this.data = @json($perkaras);
                    if (this.data.length > 0) {
                        this.columns = Object.keys(this.data[0]);
                    }
                @endif
                
                // Initialize sheets
                try {
                    const sheetsData = @json($sheets ?? []);
                    const currentSheetData = @json($currentSheet ?? '');
                    if (sheetsData && Array.isArray(sheetsData)) {
                        this.sheets = sheetsData;
                        this.activeSheet = currentSheetData;
                    }
                } catch (e) {
                    console.error('Error loading sheets:', e);
                    this.sheets = [];
                    this.activeSheet = '';
                }
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.uploadFile(file);
                }
            },

            handleDrop(event) {
                event.preventDefault();
                const files = event.dataTransfer.files;
                if (files.length > 0) {
                    this.uploadFile(files[0]);
                }
            },

            uploadFile(file) {
                // Validate file type
                const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'];
                if (!validTypes.includes(file.type)) {
                    this.error = 'Format file tidak valid. Gunakan .xlsx, .xls, atau .csv';
                    setTimeout(() => this.error = '', 4000);
                    return;
                }

                this.fileName = file.name;
                this.uploading = true;
                this.error = '';
                this.success = '';

                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

                fetch('{{ route("upload") }}', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => {
                    // Check HTTP response status
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new Error(`Server returned invalid response type. Expected JSON, got: ${contentType || 'unknown'}`);
                        });
                    }
                    
                    return response.json();
                })
                .then(result => {
                    this.uploading = false;
                    if (result.success) {
                        this.success = result.message;
                        // Reload data by fetching fresh from server
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        this.error = result.message || 'Terjadi kesalahan saat upload';
                    }
                })
                .catch(err => {
                    this.uploading = false;
                    this.error = 'Terjadi kesalahan: ' + err.message;
                    console.error('Upload error:', err);
                });
            },

            clearData() {
                if (confirm('Yakin ingin menghapus semua data?')) {
                    fetch('{{ route("clear") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            this.data = [];
                            this.columns = [];
                            this.fileName = '';
                            this.sheets = [];
                            this.activeSheet = '';
                        }
                    });
                }
            },

            viewSheet(sheetName) {
                if (!sheetName) return;
                
                this.uploading = true;
                
                fetch(`/sheet/${encodeURIComponent(sheetName)}`)
                    .then(response => response.json())
                    .then(result => {
                        this.uploading = false;
                        if (result.success) {
                            this.data = result.data;
                            if (this.data.length > 0) {
                                this.columns = Object.keys(this.data[0]);
                            }
                            this.activeSheet = sheetName;
                            this.success = `Data dari sheet "${sheetName}" berhasil dimuat (${result.count} baris)`;
                            setTimeout(() => this.success = '', 3000);
                        } else {
                            this.error = result.message;
                        }
                    })
                    .catch(err => {
                        this.uploading = false;
                        this.error = 'Error loading sheet: ' + err.message;
                    });
            }
        };
    }
</script>

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
