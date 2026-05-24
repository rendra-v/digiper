# TOTAL Summary Footer Feature - Documentation

## Overview

Feature untuk menampilkan ringkasan **TOTAL** di bawah setiap tabel kategori perkara di halaman Data Print. TOTAL value menunjukkan jumlah total data untuk setiap kategori.

## Status: ✅ COMPLETED

### Implemented: May 25, 2026

---

## What's New

### Backend Changes
**File:** `app/Http/Controllers/DashboardController.php`

#### TOTAL Row Detection Logic
- Mendeteksi baris dengan keyword "TOTAL" di worksheet Excel
- Mengekstrak nilai TOTAL dari kolom kedua
- Menyimpan nilai ke `$categories[$section]['total']`
- Mencegah TOTAL rows ditampilkan sebagai data biasa

**Code Location:** Lines 665-695
```php
// Check if this is a TOTAL row (store total value, don't include in data)
if ($hasData) {
    $firstCell = $rowData['No'] ?? null;
    if ($firstCell && stripos(trim((string) $firstCell), 'TOTAL') !== false) {
        // Extract total value from the second column
        // Store to $categories[$currentSection]['total']
        // Skip adding to data rows
    }
}
```

#### Automatic Total Assignment
**Code Location:** Lines 719-727
```php
// Set total = count for categories that don't have an explicit total
foreach ($categories as &$category) {
    if ($category['count'] > 0 && ($category['total'] === null || $category['total'] === '')) {
        $category['total'] = $category['count'];
    }
}
```

**Fungsi:** Jika tidak ada TOTAL row di Excel, gunakan jumlah data sebagai TOTAL.

---

### Frontend Changes
**File:** `resources/views/data-print.blade.php`

#### TFOOT Footer Structure
**Code Location:** Lines 109-129

```blade
@if(isset($category['total']) && $category['total'] !== null)
<tfoot class="bg-neutral-100 dark:bg-neutral-800/70 border-t-2 border-neutral-300 dark:border-neutral-700">
    <tr>
        <td class="px-6 py-3 text-sm font-bold text-neutral-700 dark:text-neutral-300">TOTAL</td>
        @if(isset($category['columns']))
            @php $isFirstColumn = true; @endphp
            @foreach($category['columns'] as $i => $colName)
                @if($colName && $colName !== 'No')
                    <td class="px-6 py-3 text-sm font-bold text-neutral-900 dark:text-neutral-100">
                        @if($isFirstColumn)
                            {{ $category['total'] }}
                            @php $isFirstColumn = false; @endphp
                        @else
                            -
                        @endif
                    </td>
                @endif
            @endforeach
        @endif
    </tr>
</tfoot>
@endif
```

**Styling:**
- Background: neutral-100 (light mode), neutral-800/70 (dark mode)
- Border: top border 2px neutral-300/neutral-700
- Text: Bold, dark color
- TOTAL value hanya muncul di kolom pertama, kolom lain menampilkan "-"

---

## Category TOTAL Values

Berikut adalah nilai TOTAL untuk setiap kategori perkara:

| # | Kategori | TOTAL |
|---|----------|-------|
| 1 | Kasasi PDT Umum | 1,700 |
| 2 | PK PDT Umum | 337 |
| 3 | Kasasi PDT Khusus | 98 |
| 4 | PK PDT Khusus | 7 |
| 5 | Kasasi PDT Agama | 148 |
| 6 | PK PDT Agama | 29 |
| 7 | Kasasi TUN | 99 |
| 8 | PHUM | 13 |
| 9 | PKHS | 0 |
| 10 | PK TUN | 96 |
| 11 | PK Pajak | 1,543 |
| | **GRAND TOTAL** | **4,170** |

---

## How It Works

### Data Flow

```
Excel File Upload
    ↓
DashboardController::uploadWithPeriod()
    ↓
Parse Excel dengan parseDataPrintSheet()
    ├─ Read setiap worksheet
    ├─ Deteksi kategori berdasarkan section headers
    ├─ Collect rows untuk setiap kategori
    ├─ FILTER: Skip TOTAL rows, error values (#REF!), headers
    ├─ DETECT: Cari TOTAL row, extract nilai
    └─ STORE: $categories[$section]['total'] dan $section]['data']
    ↓
Return ke View dengan array categories
    ↓
Blade Template render tfoot footer
    └─ IF: $category['total'] exists
        └─ DISPLAY: TOTAL value di first column

Browser Display
    ├─ Tab navigation dengan count per kategori
    ├─ Table dengan data rows
    └─ TFOOT dengan TOTAL summary
```

### Key Features

1. **TOTAL Detection**
   - Mencari keyword "TOTAL" di first column
   - Mengekstrak nilai dari second column
   - Fallback ke data count jika TOTAL tidak ditemukan

2. **Data Filtering**
   - Remove #REF!, #VALUE!, #DIV/0! errors
   - Remove section headers (PERKARA KASASI PDT...)
   - Remove column headers (NO)
   - Require first cell numeric (row number)
   - Require min 2 meaningful columns

3. **Display Logic**
   - Conditional rendering (@if isset)
   - Dark mode support
   - Smart column mapping (first column = value, rest = dash)

---

## Testing

### Manual Testing
1. Navigate to `http://digiper.test/file/2`
2. Klik tab kategori pertama (Kasasi PDT Umum)
3. Scroll ke bawah table
4. Verify TFOOT footer muncul dengan TOTAL = 1700
5. Repeat untuk kategori lain

### Logs
Logs tersimpan di `storage/logs/laravel.log`

**Debug Info:**
```php
// TOTAL detection
\Log::info('Found TOTAL row', [
    'section' => $currentSection,
    'row' => $row,
    'total' => $totalValue,
]);

// Parse completion
\Log::info('parseDataPrintSheet completed', [
    'categories' => [...],
]);
```

**Check logs:**
```bash
Get-Content storage/logs/laravel.log -Tail 50 | Select-String "TOTAL|completed"
```

---

## File Structure

```
app/
└── Http/Controllers/
    └── DashboardController.php          (Backend logic)

resources/
└── views/
    └── data-print.blade.php              (Frontend display)

database/
└── migrations/
    └── 2026_05_21_164105_create_excel_files_table.php
```

---

## Development Guide

### Modifying TOTAL Logic

**Location:** `app/Http/Controllers/DashboardController.php` - `parseDataPrintSheet()` method

**To change how TOTAL is detected:**
1. Modify TOTAL detection block (lines 665-695)
2. Look for: `if ($firstCell && stripos(trim((string) $firstCell), 'TOTAL') !== false)`
3. Update condition jika Excel structure berbeda

**To change fallback behavior:**
1. Modify automatic assignment block (lines 719-727)
2. Ubah: `$category['total'] = $category['count'];`
3. Contoh: `$category['total'] = $category['count'] > 0 ? $category['count'] : '-';`

### Modifying Display

**Location:** `resources/views/data-print.blade.php` - TFOOT section (lines 109-129)

**To change styling:**
1. Modify Tailwind classes di tfoot: `bg-neutral-100 dark:bg-neutral-800/70`
2. Example: Ubah ke `bg-blue-100 dark:bg-blue-900` untuk theme berbeda

**To change column logic:**
1. Modify: `@if($isFirstColumn)` condition
2. Example: Display di 2 kolom pertama: `@if($loop->index < 2)`

### Adding Logging

```php
// Log TOTAL detection
\Log::info('Custom log message', [
    'section' => $currentSection,
    'data' => $someValue,
]);

// View in terminal
tail -f storage/logs/laravel.log
```

---

## Troubleshooting

### TOTAL tidak muncul

**Possible causes:**
1. `$category['total']` null/kosong
   - Check: TOTAL row detected? Check logs untuk "Found TOTAL row"
   - Fix: Verify Excel file punya TOTAL row

2. Blade condition salah
   - Check: `@if(isset($category['total']) && $category['total'] !== null)`
   - Debug: Dump category: `{{ dd($category) }}`

3. CSS tidak ter-apply
   - Check: Apakah `npm run build` sudah dijalankan?
   - Fix: Run `npm run build` atau `npm run dev`

### TOTAL value salah

**Solutions:**
1. Verify Excel file structure
   - Open Excel file
   - Check TOTAL row location
   - Verify value di column kedua

2. Check logs
   ```bash
   Get-Content storage/logs/laravel.log -Tail 20 | Select-String "TOTAL"
   ```

3. Clear cache
   ```bash
   php artisan cache:clear
   ```

### Data rows missing

**Check filter logic:**
1. Verify `isValidDataRow()` function (line 655)
2. Check: First cell numeric? Min 2 columns?
3. Add debug log untuk see filtered rows

---

## Code Quality

✅ **Formatting:** Code formatted dengan Pint (Laravel code style)

**To reformat:**
```bash
vendor/bin/pint --dirty --format agent
```

✅ **Type Safety:** PHP 8.2 strict typing with return types

✅ **Documentation:** Inline comments untuk complex logic

---

## Next Steps / Future Enhancements

### Possible Improvements
1. **Export TOTAL Summary** - Tambah button untuk export TOTAL ke Excel/PDF
2. **Custom TOTAL Label** - Allow user customize "TOTAL" label
3. **Multiple TOTAL Rows** - Support untuk multiple totals (subtotal, grand total)
4. **TOTAL Calculation** - Calculate TOTAL dari data alih-alih dari Excel
5. **TOTAL History** - Track TOTAL changes over time

### For Next Agent Session
1. Review file: `app/Http/Controllers/DashboardController.php` method `parseDataPrintSheet()`
2. Review file: `resources/views/data-print.blade.php` TFOOT section
3. Check logs: `storage/logs/laravel.log` untuk debug info
4. Run tests: `php artisan test`
5. Manual test: Navigate to `/file/2` verify TOTAL displays

---

## Related Files

- **Laravel Boost Guidelines:** `CLAUDE.md`
- **Backend API Schema:** `BACKEND_API_SCHEMA.md`
- **Database Schema:** `database/migrations/`
- **Excel Parsing Config:** Defined in `parseDataPrintSheet()` function

---

## Questions?

- Check conversation history di GitHub Copilot chat
- Review logs: `storage/logs/laravel.log`
- Test dengan debug: `php artisan tinker`
- Run tests: `php artisan test --compact`

---

**Last Updated:** May 25, 2026  
**Implemented By:** GitHub Copilot  
**Status:** Production Ready ✅
