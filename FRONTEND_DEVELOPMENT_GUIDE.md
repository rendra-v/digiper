# Frontend Development Guide - DIGIPER
## UI/UX Implementation & Component Development

**Versi:** 1.0  
**Target Audience:** Frontend Developer / UI Designer  
**Stack:** Blade Templates, Tailwind CSS v4, Alpine.js  

---

## 📋 Daftar Isi

1. [Quick Start](#quick-start)
2. [Project Structure](#project-structure)
3. [Design System](#design-system)
4. [Component Library](#component-library)
5. [Page Implementation](#page-implementation)
6. [Dark Mode System](#dark-mode-system)
7. [Responsive Design](#responsive-design)
8. [Performance Tips](#performance-tips)
9. [Common Tasks](#common-tasks)

---

## 🚀 Quick Start

### Setup Development Environment

```bash
# Clone & install
git clone <repository>
cd digiper
npm install
composer install

# Copy environment
cp .env.example .env
php artisan key:generate

# Migrate database
php artisan migrate --seed

# Start development servers
php artisan serve          # Terminal 1: Backend (http://localhost:8000)
npm run dev              # Terminal 2: Frontend assets (watch mode)

# Optional: Use Laravel Herd
herd sites               # List all sites
# Aplikasi akan tersedia di: http://digiper.test/
```

### Key Commands

```bash
# Blade templating
php artisan make:view perkaras.index

# Tailwind CSS dev
npm run dev              # Watch mode with hot reload

# Build for production
npm run build

# Format code
vendor/bin/pint          # PHP formatting
npx prettier --write resources/  # JS/CSS formatting
```

---

## 📁 Project Structure

### Frontend Directory

```
resources/
├── views/                          # Blade templates
│   ├── layouts/
│   │   ├── app.blade.php          # Main layout (sidebar, header)
│   │   └── minimal.blade.php      # Minimal layout (no sidebar)
│   ├── perkaras/
│   │   ├── create.blade.php       # Upload modal
│   │   ├── index.blade.php        # Dashboard
│   │   ├── show.blade.php         # Detail page
│   │   └── recap.blade.php        # Summary recap
│   ├── laporan/
│   │   ├── cetak.blade.php        # Report template
│   │   └── export.blade.php       # Export form
│   ├── master-data/
│   │   ├── hakims/
│   │   ├── pejabats/
│   │   ├── tarifs/
│   │   └── komponen/
│   ├── components/
│   │   ├── stat-card.blade.php    # Reusable stat card
│   │   ├── table.blade.php        # Table component
│   │   ├── modal.blade.php        # Modal wrapper
│   │   ├── button.blade.php       # Button variants
│   │   ├── form-input.blade.php   # Form input wrapper
│   │   └── alert.blade.php        # Alert variants
│   └── partials/
│       ├── navbar.blade.php       # Header navigation
│       ├── sidebar.blade.php      # Sidebar menu
│       └── breadcrumb.blade.php   # Breadcrumb component
├── css/
│   └── app.css                    # Custom CSS (Tailwind directives)
├── js/
│   ├── app.js                     # Main JS entry
│   ├── bootstrap.js               # Bootstrap setup
│   ├── theme.js                   # Dark mode toggle logic
│   └── components/
│       ├── upload-handler.js      # Upload modal logic
│       └── table-filter.js        # Table filtering
└── images/                        # Static images/logos
```

### Key Files

| File | Purpose | Status |
|------|---------|--------|
| `layouts/app.blade.php` | Main layout with sidebar | ✅ Done |
| `perkaras/create.blade.php` | Upload modal | ✅ Done |
| `perkaras/index.blade.php` | Dashboard | ✅ Done |
| `perkaras/show.blade.php` | Detail view | ✅ Done |
| `tailwind.config.js` | Tailwind config | ✅ Done |
| `resources/js/theme.js` | Dark mode | ✅ Done |

---

## 🎨 Design System

### Color Palette

#### Light Mode
```css
/* Backgrounds */
--color-bg-primary: #ffffff      /* Page background */
--color-bg-secondary: #f9fafb    /* Card/section background */
--color-bg-tertiary: #f3f4f6     /* Hover state */

/* Text */
--color-text-primary: #111827     /* Main text */
--color-text-secondary: #6b7280   /* Secondary text */
--color-text-muted: #9ca3af       /* Muted/disabled text */

/* Accent */
--color-accent: #7c3aed           /* Purple - primary action */
--color-success: #10b981          /* Green - success */
--color-warning: #f59e0b          /* Amber - warning */
--color-error: #ef4444            /* Red - error */
```

#### Dark Mode
```css
/* Backgrounds */
--color-bg-primary: #111827        /* Dark background */
--color-bg-secondary: #1f2937      /* Dark card */
--color-bg-tertiary: #374151       /* Dark hover */

/* Text */
--color-text-primary: #f3f4f6      /* Light text */
--color-text-secondary: #d1d5db    /* Secondary light */
--color-text-muted: #9ca3af        /* Muted light */

/* Accent */
--color-accent: #8b5cf6            /* Purple lighter */
--color-success: #34d399           /* Green lighter */
--color-warning: #fbbf24           /* Amber lighter */
--color-error: #f87171             /* Red lighter */
```

### Typography

```css
/* Font Stack */
font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

/* Sizes */
--text-xs: 0.75rem;       /* 12px */
--text-sm: 0.875rem;      /* 14px */
--text-base: 1rem;        /* 16px */
--text-lg: 1.125rem;      /* 18px */
--text-xl: 1.25rem;       /* 20px */
--text-2xl: 1.5rem;       /* 24px */
--text-3xl: 1.875rem;     /* 30px */

/* Weights */
font-weight: 400;         /* Regular */
font-weight: 500;         /* Medium */
font-weight: 600;         /* SemiBold */
font-weight: 700;         /* Bold */
```

### Spacing Scale

```
4px   = 1  unit
8px   = 2  units
12px  = 3  units
16px  = 4  units
24px  = 6  units
32px  = 8  units
48px  = 12 units
64px  = 16 units
```

**Tailwind usage:** `p-4` = padding 16px, `m-2` = margin 8px, etc.

### Border Radius

```
rounded-sm = 0.125rem    (2px)
rounded    = 0.375rem    (6px)
rounded-lg = 0.5rem      (8px)
rounded-xl = 0.75rem     (12px)
rounded-2xl = 1rem       (16px)
rounded-full = 9999px    (circle)
```

### Shadows

```
shadow-sm      = 0 1px 2px 0 rgba(0,0,0,0.05)
shadow         = 0 1px 3px 0 rgba(0,0,0,0.1)
shadow-lg      = 0 10px 15px -3px rgba(0,0,0,0.1)
shadow-xl      = 0 20px 25px -5px rgba(0,0,0,0.1)
shadow-2xl     = 0 25px 50px -12px rgba(0,0,0,0.25)
```

---

## 🧩 Component Library

### 1. Stat Card Component

**File:** `resources/views/components/stat-card.blade.php`

```blade
@props(['label', 'value', 'icon' => '📊', 'trend' => null, 'color' => 'purple'])

<div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $value }}</p>
            @if($trend)
                <p class="text-xs {{ $trend > 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                    {{ abs($trend) }}% {{ $trend > 0 ? 'increase' : 'decrease' }}
                </p>
            @endif
        </div>
        <div class="text-4xl">{{ $icon }}</div>
    </div>
</div>

{{-- Usage --}}
<x-stat-card 
    label="Total Perkara" 
    value="125" 
    icon="📋"
    trend="5"
/>
```

### 2. Data Table Component

**File:** `resources/views/components/table.blade.php`

```blade
@props(['headers', 'data', 'actions' => true])

<div class="overflow-x-auto rounded-lg shadow">
    <table class="min-w-full">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                @foreach($headers as $header)
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">
                        {{ $header }}
                    </th>
                @endforeach
                @if($actions)
                    <th class="px-6 py-3 text-left text-xs font-semibold">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
```

### 3. Button Variants

```blade
{{-- Primary Button --}}
<button class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition">
    Primary
</button>

{{-- Secondary Button --}}
<button class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium transition">
    Secondary
</button>

{{-- Danger Button --}}
<button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition">
    Delete
</button>

{{-- Disabled Button --}}
<button disabled class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-lg font-medium cursor-not-allowed opacity-50">
    Disabled
</button>
```

### 4. Form Input Component

```blade
@props(['label', 'name', 'type' => 'text', 'placeholder' => '', 'required' => false, 'value' => ''])

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        {{ $label }}
        @if($required) <span class="text-red-600">*</span> @endif
    </label>
    <input 
        type="{{ $type }}" 
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
        {{ $required ? 'required' : '' }}
        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
               bg-white dark:bg-gray-700 text-gray-900 dark:text-white
               focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent"
    />
</div>
```

### 5. Alert Component

```blade
@props(['type' => 'info', 'title' => '', 'message' => ''])

@php
    $colors = [
        'info' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200 border-blue-200 dark:border-blue-800',
        'success' => 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200 border-green-200 dark:border-green-800',
        'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 border-yellow-200 dark:border-yellow-800',
        'error' => 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200 border-red-200 dark:border-red-800',
    ];
@endphp

<div class="border-l-4 p-4 rounded {{ $colors[$type] }}">
    @if($title)
        <h3 class="font-semibold">{{ $title }}</h3>
    @endif
    <p>{{ $message }}</p>
</div>
```

### 6. Modal Component

```blade
@props(['id', 'title'])

<div id="{{ $id }}" class="hidden fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
            <button onclick="document.getElementById('{{ $id }}').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                ✕
            </button>
        </div>
        <div class="px-6 py-4">
            {{ $slot }}
        </div>
    </div>
</div>
```

---

## 📄 Page Implementation

### Page 1: Upload Modal (`perkaras/create.blade.php`)

**Status:** ✅ Complete  
**Layout:** Standalone (no layout extension)

**Key Elements:**
- Full-screen dark overlay
- Centered modal box
- Drag-drop zone with visual feedback
- File info display
- Action buttons (Reset, Upload)

**Usage:**
```blade
<!-- Already implemented - review at resources/views/perkaras/create.blade.php -->
```

---

### Page 2: Dashboard (`perkaras/index.blade.php`)

**Status:** ✅ Complete  
**Layout:** Extends `layouts.app`

**Sections:**
1. **Breadcrumb** - Show current page
2. **Page Header** - Title + action buttons
3. **Stats Cards** - 4 KPI cards (Total, Valid, Biaya, Hakim)
4. **Filters** - Kamar & Jenis dropdown
5. **Data Table** - Paginated list with actions
6. **Detail Button** - Opens detail page

**Key Implementation Details:**

```blade
{{-- Stats Section --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card label="Total Perkara" value="{{ $totalCount }}" icon="📋" />
    <x-stat-card label="Valid (≥90 hari)" value="{{ $validCount }}" icon="✅" />
    <x-stat-card label="Total Biaya" value="Rp {{ number_format($totalBiaya) }}" icon="💰" />
    <x-stat-card label="Total Hakim" value="{{ $hakimCount }}" icon="⚖️" />
</div>

{{-- Filter Section --}}
<div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-6 shadow">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-form-input name="search" label="Search" placeholder="Cari no. registrasi..." />
        <select name="kamar" class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 dark:bg-gray-700">
            <option value="">Semua Kamar</option>
            @foreach($kamars as $kamar)
                <option value="{{ $kamar }}">{{ $kamar }}</option>
            @endforeach
        </select>
        <select name="jenis" class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 dark:bg-gray-700">
            <option value="">Semua Jenis</option>
            @foreach($jenis as $j)
                <option value="{{ $j->id }}">{{ $j->nama }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Table Section --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold">No. Registrasi</th>
                <th class="px-6 py-3 text-left text-sm font-semibold">Tanggal Masuk</th>
                <th class="px-6 py-3 text-left text-sm font-semibold">Usia (Hari)</th>
                <th class="px-6 py-3 text-left text-sm font-semibold">Jenis</th>
                <th class="px-6 py-3 text-left text-sm font-semibold">Biaya</th>
                <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                <th class="px-6 py-3 text-left text-sm font-semibold">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($perkaras as $perkara)
                <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4 font-medium">{{ $perkara->no_registrasi }}</td>
                    <td class="px-6 py-4">{{ $perkara->tanggal_masuk->format('d M Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="badge {{ $perkara->getUsiaPerkara() >= 90 ? 'badge-green' : 'badge-red' }}">
                            {{ $perkara->getUsiaPerkara() }}
                        </span>
                    </td>
                    <td class="px-6 py-4">{{ $perkara->jenis_perkara->nama ?? '-' }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($perkara->biaya) }}</td>
                    <td class="px-6 py-4">
                        <span class="badge {{ $perkara->status_biaya == 'kena' ? 'badge-green' : 'badge-gray' }}">
                            {{ ucfirst($perkara->status_biaya) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('perkaras.show', $perkara) }}" class="btn-small btn-primary">
                            Detail
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-6">
    {{ $perkaras->links() }}
</div>
```

---

### Page 3: Detail View (`perkaras/show.blade.php`)

**Status:** ✅ Complete  
**Layout:** Extends `layouts.app`

**Sections:**
1. **Header** - Registrasi number, breadcrumb
2. **Main Content** (2-column)
   - Left: Informasi umum, Majelis, Amar
   - Right: Biaya card (sidebar)
3. **Breakdown Biaya** - Detail komponen
4. **Distribusi Honor** - Honor table
5. **Actions** - Edit, Delete, Print

---

### Page 4: Recap Summary (`perkaras/recap.blade.php`)

**Status:** ✅ Complete  
**Layout:** Extends `layouts.app`

**Sections:**
1. **Stats** - Summary cards
2. **Recap Table** - Grouped by kamar
3. **Classification Columns** - Biaya ranges
4. **Grand Total Row** - All totals

---

## 🌙 Dark Mode System

### How It Works

1. **Detection:** System detects user preference via localStorage
2. **Default:** Falls back to system preference (prefers-color-scheme)
3. **Toggle:** User can toggle in header (🌙 / ☀️)
4. **Persistence:** Choice saved to localStorage

### Implementation

**File:** `resources/js/theme.js`

```javascript
function initTheme() {
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    const theme = saved || (prefersDark ? 'dark' : 'light');
    
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    }
    
    updateThemeIcon(theme);
}

function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    const theme = isDark ? 'dark' : 'light';
    
    localStorage.setItem('theme', theme);
    updateThemeIcon(theme);
}
```

### Using Dark Mode in Templates

```blade
{{-- Tailwind dark mode classes --}}
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    Content
</div>

{{-- With custom CSS --}}
<style>
    @media (prefers-color-scheme: dark) {
        :root {
            --color-bg: #111827;
            --color-text: #f3f4f6;
        }
    }
</style>
```

---

## 📱 Responsive Design

### Breakpoints (Tailwind)

```
sm = 640px    (small screens)
md = 768px    (tablets)
lg = 1024px   (desktops)
xl = 1280px   (large desktops)
2xl = 1536px  (very large screens)
```

### Responsive Patterns

```blade
{{-- Mobile-first: default for mobile, override for larger screens --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    {{-- 1 col on mobile, 2 on tablet, 4 on desktop --}}
</div>

{{-- Hide on mobile --}}
<div class="hidden md:block">
    {{-- Shows only on tablet and up --}}
</div>

{{-- Responsive text sizes --}}
<h1 class="text-2xl md:text-3xl lg:text-4xl font-bold">Title</h1>

{{-- Responsive padding --}}
<div class="p-4 md:p-6 lg:p-8">
    {{-- 16px on mobile, 24px on tablet, 32px on desktop --}}
</div>
```

### Mobile Optimization

- **Sidebar:** Collapse on mobile (hamburger menu)
- **Tables:** Horizontal scroll or card layout on mobile
- **Modals:** Full-screen on mobile, centered on desktop
- **Touch Targets:** Minimum 44px × 44px for buttons

---

## ⚡ Performance Tips

### CSS Optimization

```javascript
// Tailwind purging - automatic in production
// Only include used classes in final CSS

// In tailwind.config.js
content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
],
```

### Image Optimization

```blade
{{-- Use appropriate image formats --}}
<img src="/images/logo.webp" alt="Logo" class="w-12 h-12" loading="lazy">

{{-- Use srcset for responsive images --}}
<img srcset="small.webp 480w, medium.webp 800w, large.webp 1200w"
     sizes="(max-width: 480px) 100vw, (max-width: 800px) 80vw, 1200px"
     src="large.webp" alt="Responsive image">
```

### Caching Headers

```blade
{{-- In layout --}}
<meta http-equiv="Cache-Control" content="public, max-age=3600">
```

### Lazy Loading

```blade
{{-- Defer non-critical JS --}}
<script defer src="{{ asset('js/app.js') }}"></script>

{{-- Lazy load images --}}
<img loading="lazy" src="...">
```

---

## 🎯 Common Tasks

### Task 1: Create New Page

```bash
# 1. Create view file
php artisan make:view pages.new-page

# 2. Create route (routes/web.php)
Route::get('/new-page', [Controller::class, 'method'])->name('new-page');

# 3. Create layout content
@extends('layouts.app')
@section('content')
    <!-- Your content -->
@endsection
```

### Task 2: Add New Component

```bash
# 1. Create component
touch resources/views/components/my-component.blade.php

# 2. Define component
@props(['title', 'content'])
<div class="...">
    <h3>{{ $title }}</h3>
    <p>{{ $content }}</p>
</div>

# 3. Use component
<x-my-component title="Hello" content="World" />
```

### Task 3: Style New Element

```css
/* 1. Add to resources/css/app.css */
@layer components {
    .btn-custom {
        @apply px-4 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition;
    }
}

/* 2. Use in blade */
<button class="btn-custom">Click me</button>
```

### Task 4: Add JavaScript Interactivity

```javascript
// resources/js/components/my-script.js
export function initMyFeature() {
    document.querySelectorAll('.my-element').forEach(el => {
        el.addEventListener('click', () => {
            // Handle click
        });
    });
}

// Import in app.js
import { initMyFeature } from './components/my-script';
initMyFeature();
```

---

## 📐 Accessibility Guidelines

### WCAG 2.1 Compliance

```blade
{{-- Always use semantic HTML --}}
<button> instead of <div class="button">
<a> instead of <div class="link">
<form> with <label>

{{-- Add ARIA labels --}}
<button aria-label="Close menu">✕</button>

{{-- Color contrast - minimum 4.5:1 for text --}}
<p class="text-gray-900 dark:text-gray-100">Sufficient contrast</p>

{{-- Keyboard navigation --}}
<button tabindex="0" @keydown.enter="handleClick">
    Keyboard accessible
</button>

{{-- Focus indicators --}}
<input class="focus:outline-none focus:ring-2 focus:ring-purple-600">
```

---

## 🧪 Testing Frontend

### Visual Testing

```bash
# Manual testing checklist
□ Test all pages on mobile (375px width)
□ Test on tablet (768px width)
□ Test on desktop (1024px+ width)
□ Test dark mode toggle
□ Test form validation
□ Test dropdown filters
□ Test pagination
□ Test responsive images
```

### Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (iOS 12+, Android 8+)

---

## 📚 Resources & Links

### Tailwind CSS
- [Documentation](https://tailwindcss.com/docs)
- [Color Palette](https://tailwindcss.com/docs/customizing-colors)
- [Components](https://tailwindcss.com/docs/responsive-design)

### Laravel Blade
- [Documentation](https://laravel.com/docs/blade)
- [Components & Slots](https://laravel.com/docs/blade#components)

### Design Tools
- [Figma](https://figma.com) - UI Design
- [Tailwind UI](https://tailwindui.com) - Component Examples

---

## 🔗 Related Documentation

- See [DOKUMENTASI_SISTEM.md](DOKUMENTASI_SISTEM.md) for overall system design
- See [FRONTEND_COMPONENTS.md](FRONTEND_COMPONENTS.md) for detailed component specs
- See [BACKEND_DEVELOPMENT_GUIDE.md](BACKEND_DEVELOPMENT_GUIDE.md) for API integration

---

**Last Updated:** Mei 2026  
**Author:** Development Team  
**Status:** Ready for Implementation
