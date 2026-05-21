<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Digiper</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body 
    x-data="themeApp()" 
    x-cloak
    :class="{ 'dark': darkMode }"
    class="bg-white dark:bg-neutral-950 text-neutral-950 dark:text-neutral-50 transition-colors duration-300"
>
    <!-- Header/Nav -->
    <div class="border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950">
        <div class="max-w-7xl mx-auto px-8 py-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Digiper</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Data Management System</p>
            </div>
            
            <!-- Theme Toggle -->
            <button 
                @click="darkMode = !darkMode"
                class="relative inline-flex h-9 w-16 items-center rounded-full border border-neutral-200 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-800 px-1 transition-all duration-300 hover:border-neutral-300 dark:hover:border-neutral-600"
            >
                <span 
                    x-show="!darkMode"
                    class="h-7 w-7 rounded-full bg-white flex items-center justify-center transition-all duration-300"
                    :class="darkMode ? 'translate-x-8' : 'translate-x-0'"
                >
                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zM4.22 4.22a1 1 0 011.415 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zm11.313 1.414a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM4 10a1 1 0 110-2 1 1 0 010 2zm12-1a1 1 0 11-2 0 1 1 0 012 0zm-1.22 5.78a1 1 0 00-1.415 0l-.707.707a1 1 0 001.414 1.414l.707-.707a1 1 0 000-1.414zm-9.556 0a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM10 18a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span 
                    x-show="darkMode"
                    class="h-7 w-7 rounded-full bg-neutral-700 flex items-center justify-center transition-all duration-300"
                    :class="!darkMode ? 'translate-x-0' : 'translate-x-8'"
                >
                    <svg class="w-4 h-4 text-slate-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </span>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-8 py-12">
        @yield('content')
    </div>

    @yield('scripts')

    <script>
        // Dark mode stylesheet
        const darkStylesheet = document.createElement('style');
        darkStylesheet.textContent = `
            :root.dark {
                color-scheme: dark;
            }
            
            .dark {
                background-color: #0a0a0a !important;
                color: #fafafa !important;
            }
            
            .dark .border-neutral-200 {
                border-color: #262626 !important;
            }
            
            .dark .border-neutral-200 {
                border-color: #262626 !important;
            }
            
            .dark .border-neutral-100 {
                border-color: #262626 !important;
            }
            
            .dark .bg-white {
                background-color: #0a0a0a !important;
            }
            
            .dark .bg-neutral-50 {
                background-color: #171717 !important;
            }
            
            .dark .bg-neutral-100 {
                background-color: #171717 !important;
            }
            
            .dark .bg-neutral-950 {
                background-color: #0a0a0a !important;
            }
            
            .dark .text-neutral-500 {
                color: #737373 !important;
            }
            
            .dark .text-neutral-600 {
                color: #525252 !important;
            }
            
            .dark .text-neutral-400 {
                color: #a3a3a3 !important;
            }
            
            .dark .text-neutral-700 {
                color: #404040 !important;
            }
            
            .dark .border-neutral-700 {
                border-color: #404040 !important;
            }
            
            .dark .bg-neutral-800 {
                background-color: #262626 !important;
            }
            
            .dark .border-neutral-800 {
                border-color: #262626 !important;
            }
            
            .dark .hover\\:border-neutral-300:hover {
                border-color: #d4d4d4 !important;
            }
            
            .dark .dark\\:border-neutral-600:hover {
                border-color: #525252 !important;
            }
        `;
        document.head.appendChild(darkStylesheet);

        // Theme initialization before Alpine loads
        (function() {
            const isDark = localStorage.getItem('theme') === 'dark';
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
        })();

        function themeApp() {
            return {
                darkMode: localStorage.getItem('theme') === 'dark',
                init() {
                    // Watch for changes
                    this.$watch('darkMode', value => {
                        localStorage.setItem('theme', value ? 'dark' : 'light');
                        if (value) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    });
                }
            };
        }
    </script>
</body>
</html>
