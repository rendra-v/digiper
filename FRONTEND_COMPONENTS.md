# Frontend Components & Templates Specification
## Comprehensive Component Library Documentation

**Versi:** 1.0  
**Target Audience:** Frontend Developer / UI Component Developer  
**Purpose:** Detailed specs for all reusable frontend components  

---

## 📋 Component Index

1. [Layout Components](#layout-components)
2. [Data Display Components](#data-display-components)
3. [Form Components](#form-components)
4. [Feedback Components](#feedback-components)
5. [Navigation Components](#navigation-components)
6. [Modal & Overlay Components](#modal--overlay-components)
7. [Badge & Tag Components](#badge--tag-components)
8. [State Templates](#state-templates)

---

## 🏗️ Layout Components

### 1. Main Application Layout

**File:** `resources/views/layouts/app.blade.php`

**Purpose:** Wraps all authenticated pages with sidebar, header, footer

**Structure:**
```
┌─────────────────────────────────────────┐
│         Header (Navbar)                 │ 64px
├─────────┬───────────────────────────────┤
│         │                               │
│ Sidebar │    Main Content Area          │
│ 256px   │    (flex-1)                   │
│         │                               │
│         │                               │
└─────────┴───────────────────────────────┘
```

**HTML Structure:**
```html
<html class="scroll-smooth">
  <head>
    {{-- Meta tags, Vite assets --}}
  </head>
  <body class="bg-white dark:bg-gray-900">
    
    <div class="min-h-screen flex">
      <!-- Sidebar -->
      <aside class="w-64 bg-white dark:bg-gray-800 border-r">
        <x-sidebar />
      </aside>
      
      <!-- Main Content -->
      <div class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="h-16 bg-white dark:bg-gray-800 border-b">
          <x-navbar />
        </header>
        
        <!-- Content -->
        <main class="flex-1 overflow-y-auto p-6">
          @yield('content')
        </main>
      </div>
    </div>
    
  </body>
</html>
```

**Props:** None (layout wrapper)

**Usage:**
```blade
@extends('layouts.app')
@section('content')
    <h1>Page Title</h1>
    <!-- Page content -->
@endsection
```

---

### 2. Minimal Layout (No Sidebar)

**File:** `resources/views/layouts/minimal.blade.php`

**Purpose:** For modals, login pages, upload pages

**Usage:**
```blade
@extends('layouts.minimal')
@section('content')
    {{-- Full-screen content without sidebar --}}
@endsection
```

---

## 📊 Data Display Components

### 1. Statistic Card

**File:** `resources/views/components/stat-card.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| label | string | required | Card title |
| value | string/int | required | Main value to display |
| icon | string | '📊' | Emoji icon |
| trend | int | null | Percentage change (optional) |
| color | string | 'purple' | Card accent color |
| href | string | null | Link if clickable |

**Example:**
```blade
<x-stat-card 
    label="Total Perkara" 
    value="125" 
    icon="📋"
    trend="5"
    color="purple"
    href="{{ route('perkaras.index') }}"
/>
```

**Rendering:**
```
┌─────────────────────────────┐
│  📋 Total Perkara   │       │
│  125                        │
│  ↑ 5% increase      │       │
└─────────────────────────────┘
```

---

### 2. Data Table

**File:** `resources/views/components/table.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| headers | array | required | Column headers |
| data | collection | required | Data rows |
| actions | bool | true | Show actions column |
| sortable | bool | false | Enable column sorting |
| paginate | bool | true | Show pagination |

**Usage:**
```blade
<x-table :headers="['No Reg', 'Tanggal', 'Usia', 'Biaya']">
    @foreach($perkaras as $perkara)
        <tr>
            <td>{{ $perkara->no_registrasi }}</td>
            <td>{{ $perkara->tanggal_masuk->format('d M Y') }}</td>
            <td>{{ $perkara->getUsiaPerkara() }} hari</td>
            <td>Rp {{ number_format($perkara->biaya) }}</td>
        </tr>
    @endforeach
</x-table>
```

**Features:**
- Hover row highlight
- Responsive horizontal scroll on mobile
- Zebra striping (alternate row colors)
- Sticky header on scroll
- Dark mode support

---

### 3. Card Component

**File:** `resources/views/components/card.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| title | string | null | Card title |
| subtitle | string | null | Card subtitle |
| padding | string | '6' | Padding size (Tailwind scale) |

**Usage:**
```blade
<x-card title="Informasi Umum" subtitle="Detail Perkara">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">No Registrasi</p>
            <p class="font-semibold">225/PK/2025</p>
        </div>
        <!-- More fields -->
    </div>
</x-card>
```

---

### 4. Badge Component

**File:** `resources/views/components/badge.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| text | string | required | Badge text |
| variant | enum | 'gray' | Color variant |
| size | enum | 'md' | Size variant |

**Variants:**
```blade
{{-- Gray --}}
<x-badge text="Draft" variant="gray" />

{{-- Green (Success) --}}
<x-badge text="Valid (≥90 hari)" variant="green" />

{{-- Red (Danger) --}}
<x-badge text="Invalid" variant="red" />

{{-- Yellow (Warning) --}}
<x-badge text="Pending" variant="yellow" />

{{-- Blue (Info) --}}
<x-badge text="Processing" variant="blue" />

{{-- Purple (Primary) --}}
<x-badge text="Active" variant="purple" />
```

**Rendering:**
```
┌──────────────────┐
│  ✓ Valid (≥90h)  │  <- Green badge
└──────────────────┘
```

---

## 📝 Form Components

### 1. Text Input

**File:** `resources/views/components/form-input.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| name | string | required | Input name attribute |
| label | string | null | Label text |
| type | enum | 'text' | Input type |
| placeholder | string | '' | Placeholder text |
| value | string | '' | Pre-filled value |
| required | bool | false | Required indicator |
| error | string | null | Error message |
| help | string | null | Help text |

**Types Supported:**
- text
- email
- password
- number
- date
- time
- search
- tel
- url

**Usage:**
```blade
<x-form-input 
    name="no_registrasi" 
    label="No. Registrasi"
    type="text"
    placeholder="225/PK/TUN/2025"
    required="true"
    error="$errors->first('no_registrasi')"
/>
```

**Rendering:**
```
┌─────────────────────────────┐
│ No. Registrasi *            │ (Label + required marker)
│ ┌───────────────────────────┐│
│ │ 225/PK/TUN/2025           ││ (Input field with placeholder)
│ └───────────────────────────┘│
│ Help text or error message  │ (Optional)
└─────────────────────────────┘
```

---

### 2. Select Dropdown

**File:** `resources/views/components/form-select.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| name | string | required | Select name |
| label | string | null | Label text |
| options | array | required | Options array |
| selected | string | null | Pre-selected value |
| required | bool | false | Required |
| multiple | bool | false | Allow multiple select |

**Usage:**
```blade
<x-form-select 
    name="jenis_perkara_id"
    label="Jenis Perkara"
    :options="$jenisPerkara"
    selected="{{ old('jenis_perkara_id', $perkara->jenis_perkara_id) }}"
    required="true"
/>
```

---

### 3. Textarea

**File:** `resources/views/components/form-textarea.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| name | string | required | Textarea name |
| label | string | null | Label text |
| value | string | '' | Pre-filled content |
| rows | int | 4 | Number of rows |
| placeholder | string | '' | Placeholder |
| required | bool | false | Required |

**Usage:**
```blade
<x-form-textarea 
    name="amar"
    label="Amar Putusan"
    :value="$perkara->amar"
    rows="6"
    required="true"
/>
```

---

### 4. Form Group (Wrapper)

**File:** `resources/views/components/form-group.blade.php`

**Purpose:** Wrapper for multiple form fields in columns

**Usage:**
```blade
<x-form-group cols="2">
    <x-form-input name="nama_p1" label="Hakim P1" />
    <x-form-input name="nama_p2" label="Hakim P2" />
</x-form-group>
```

---

## 💬 Feedback Components

### 1. Alert Component

**File:** `resources/views/components/alert.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| type | enum | 'info' | Alert type |
| title | string | null | Alert title |
| message | string | required | Alert message |
| icon | bool | true | Show icon |
| dismissible | bool | true | Show close button |

**Types:**
```blade
{{-- Info --}}
<x-alert type="info" title="Info" message="This is an information message" />

{{-- Success --}}
<x-alert type="success" title="Success" message="Operation completed successfully" />

{{-- Warning --}}
<x-alert type="warning" title="Warning" message="Please check your input" />

{{-- Error --}}
<x-alert type="error" title="Error" message="Something went wrong" />
```

---

### 2. Toast Notification

**File:** `resources/views/components/toast.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| type | enum | 'info' | Toast type |
| message | string | required | Message |
| duration | int | 3000 | Show duration (ms) |

**Usage (JavaScript):**
```javascript
showToast('File uploaded successfully', 'success', 3000);
```

---

### 3. Loading Spinner

**File:** `resources/views/components/spinner.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| size | enum | 'md' | Spinner size |
| text | string | 'Loading...' | Loading text |

**Usage:**
```blade
<x-spinner size="md" text="Importing data..." />
```

---

## 🧭 Navigation Components

### 1. Sidebar Navigation

**File:** `resources/views/partials/sidebar.blade.php`

**Structure:**
```
Logo / Brand
├─ Dashboard
├─ Upload Data
├─ Rekapitulasi
├─ Laporan
└─ Master Data
   ├─ Hakim
   ├─ Pejabat
   └─ Tarif
```

**Menu Items:**
```blade
<div class="space-y-2">
    <x-nav-item href="{{ route('dashboard') }}" icon="📊">
        Dashboard
    </x-nav-item>
    
    <x-nav-item href="{{ route('perkaras.create') }}" icon="📤">
        Upload Data
    </x-nav-item>
    
    <!-- More items -->
</div>
```

---

### 2. Navbar / Header

**File:** `resources/views/partials/navbar.blade.php`

**Contains:**
- Breadcrumb navigation
- Page title
- Action buttons (right side)
- User profile dropdown
- Theme toggle (🌙/☀️)

**Usage:**
```blade
<x-navbar 
    title="Dashboard" 
    breadcrumbs="{{ route('home') }} > Dashboard"
/>
```

---

### 3. Breadcrumb Component

**File:** `resources/views/components/breadcrumb.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| items | array | required | Breadcrumb items |

**Usage:**
```blade
<x-breadcrumb :items="[
    ['label' => 'Home', 'href' => route('dashboard')],
    ['label' => 'Perkaras', 'href' => route('perkaras.index')],
    ['label' => 'Detail', 'href' => null],
]" />
```

**Rendering:**
```
Home > Perkaras > Detail
```

---

## 🪟 Modal & Overlay Components

### 1. Modal Component

**File:** `resources/views/components/modal.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| id | string | required | Modal ID |
| title | string | required | Modal title |
| size | enum | 'md' | Size (sm/md/lg/xl) |
| closable | bool | true | Show close button |

**Usage:**
```blade
<x-modal id="editModal" title="Edit Perkara" size="lg">
    <form method="POST" action="{{ route('perkaras.update', $perkara) }}">
        <x-form-input name="no_registrasi" label="No. Registrasi" />
        {{-- More fields --}}
        <div class="flex gap-2 justify-end mt-6">
            <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
        </div>
    </form>
</x-modal>

<!-- Trigger button -->
<button class="btn-primary" onclick="openModal('editModal')">Edit</button>
```

**JavaScript API:**
```javascript
openModal('editModal');      // Open modal
closeModal('editModal');     // Close modal
toggleModal('editModal');    // Toggle modal
```

---

### 2. Dialog / Confirmation

**File:** `resources/views/components/dialog.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| id | string | required | Dialog ID |
| title | string | required | Dialog title |
| message | string | required | Dialog message |
| confirmText | string | 'Confirm' | Confirm button text |
| cancelText | string | 'Cancel' | Cancel button text |

**Usage:**
```blade
<x-dialog 
    id="deleteConfirm" 
    title="Delete Perkara"
    message="Are you sure you want to delete this record? This action cannot be undone."
    confirmText="Delete"
    cancelText="Cancel"
    @confirm="deletePerkara()"
/>

<button onclick="showDialog('deleteConfirm')">Delete</button>
```

---

## 🏷️ Badge & Tag Components

### 1. Status Badge

**File:** `resources/views/components/status-badge.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| status | string | required | Status value |
| type | enum | 'generic' | Badge type |

**Status Examples:**
```blade
{{-- Biaya Status --}}
<x-status-badge status="kena" type="biaya" />       → ✓ Kena Biaya (Green)
<x-status-badge status="belum_kena" type="biaya" /> → ○ Belum Kena (Gray)

{{-- Validation Status --}}
<x-status-badge status="valid" type="validation" /> → ✓ Valid (Green)
<x-status-badge status="invalid" type="validation" /> → ✗ Invalid (Red)

{{-- Processing Status --}}
<x-status-badge status="pending" type="process" />    → ⏳ Pending (Yellow)
<x-status-badge status="completed" type="process" />  → ✓ Completed (Green)
<x-status-badge status="failed" type="process" />     → ✗ Failed (Red)
```

---

### 2. Age / Usia Badge

**File:** `resources/views/components/usia-badge.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| days | int | required | Number of days |
| threshold | int | 90 | Threshold for coloring |

**Rendering:**
```blade
<x-usia-badge :days="$perkara->getUsiaPerkara()" />

<!-- Output examples: -->
<!-- <span class="badge-green">405 hari</span>  if >= 90 -->
<!-- <span class="badge-red">45 hari</span>    if < 90 -->
```

---

## 📋 State Templates

### 1. Empty State

**File:** `resources/views/components/empty-state.blade.php`

**Props:**
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| icon | string | required | Emoji icon |
| title | string | required | Empty state title |
| message | string | required | Empty state message |
| action | string | null | Action button text |
| actionUrl | string | null | Action button URL |

**Usage:**
```blade
<x-empty-state 
    icon="📤"
    title="No Data"
    message="No perkara found. Upload data to get started."
    action="Upload Data"
    actionUrl="{{ route('perkaras.create') }}"
/>
```

**Rendering:**
```
┌──────────────────────────────┐
│                              │
│           📤                 │
│                              │
│    No Data                   │
│    No perkara found.         │
│    Upload data to get        │
│    started.                  │
│                              │
│  [Upload Data]               │
│                              │
└──────────────────────────────┘
```

---

### 2. Error State

**File:** `resources/views/components/error-state.blade.php`

**Usage:**
```blade
<x-error-state 
    icon="❌"
    title="Error"
    message="Failed to load data. Please try again."
    action="Retry"
    actionUrl="{{ route('perkaras.index') }}"
/>
```

---

### 3. Loading State

**File:** `resources/views/components/loading-state.blade.php`

**Usage:**
```blade
<x-loading-state message="Importing data..." />
```

---

## 🎨 Utility Classes

### Button Classes

```blade
{{-- Primary Button --}}
class="btn btn-primary"

{{-- Secondary Button --}}
class="btn btn-secondary"

{{-- Danger Button --}}
class="btn btn-danger"

{{-- Small Button --}}
class="btn btn-sm btn-primary"

{{-- Large Button --}}
class="btn btn-lg btn-primary"

{{-- Disabled Button --}}
class="btn btn-primary disabled"

{{-- Block Button (full width) --}}
class="btn btn-primary w-full"

{{-- Icon Button --}}
class="btn-icon btn-primary"
```

### Text Utilities

```blade
{{-- Text Colors --}}
class="text-gray-900 dark:text-white"     {{-- Primary text --}}
class="text-gray-600 dark:text-gray-400"  {{-- Secondary text --}}
class="text-red-600"                      {{-- Error text --}}
class="text-green-600"                    {{-- Success text --}}

{{-- Text Sizes --}}
class="text-xs"   {{-- 12px --}}
class="text-sm"   {{-- 14px --}}
class="text-base" {{-- 16px --}}
class="text-lg"   {{-- 18px --}}
class="text-xl"   {{-- 20px --}}

{{-- Font Weights --}}
class="font-normal"     {{-- 400 --}}
class="font-medium"     {{-- 500 --}}
class="font-semibold"   {{-- 600 --}}
class="font-bold"       {{-- 700 --}}
```

---

## 📚 Component Inventory

| Component | File | Status | Last Update |
|-----------|------|--------|-------------|
| stat-card | components/stat-card.blade.php | ✅ Done | May 2026 |
| table | components/table.blade.php | ✅ Done | May 2026 |
| card | components/card.blade.php | ✅ Done | May 2026 |
| badge | components/badge.blade.php | ✅ Done | May 2026 |
| form-input | components/form-input.blade.php | ✅ Done | May 2026 |
| form-select | components/form-select.blade.php | ✅ Done | May 2026 |
| alert | components/alert.blade.php | ✅ Done | May 2026 |
| modal | components/modal.blade.php | ✅ Done | May 2026 |
| empty-state | components/empty-state.blade.php | ✅ Done | May 2026 |
| navbar | partials/navbar.blade.php | ✅ Done | May 2026 |
| sidebar | partials/sidebar.blade.php | ✅ Done | May 2026 |

---

## 🔗 Related Files

- See [FRONTEND_DEVELOPMENT_GUIDE.md](FRONTEND_DEVELOPMENT_GUIDE.md) for styling conventions
- See [DOKUMENTASI_SISTEM.md](DOKUMENTASI_SISTEM.md) for system architecture
- See [BACKEND_API_SCHEMA.md](BACKEND_API_SCHEMA.md) for API integration

---

**Last Updated:** Mei 2026  
**Status:** Reference Documentation - Ready to Use
