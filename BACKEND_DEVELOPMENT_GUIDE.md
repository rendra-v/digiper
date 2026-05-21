# Backend Development Guide - DIGIPER
## API, Logic, & Database Implementation

**Versi:** 1.0  
**Target Audience:** Backend Developer / API Developer  
**Stack:** Laravel 12, PHP 8.4, MySQL, Eloquent ORM  

---

## 📋 Daftar Isi

1. [Project Setup](#project-setup)
2. [Folder Structure](#folder-structure)
3. [Database & Migrations](#database--migrations)
4. [Model & Relationships](#model--relationships)
5. [Controllers & Routes](#controllers--routes)
6. [Services & Business Logic](#services--business-logic)
7. [Excel Import](#excel-import)
8. [Validation & Error Handling](#validation--error-handling)
9. [Testing](#testing)
10. [Best Practices](#best-practices)

---

## 🚀 Project Setup

### Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database
# In .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=digiper
DB_USERNAME=root
DB_PASSWORD=

# Or use Laravel Herd
herd database create digiper
```

### Initial Setup Commands

```bash
# Install dependencies
composer install

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan migrate --seed

# Cache configuration
php artisan config:cache

# Start development server
php artisan serve          # http://localhost:8000

# Or use Herd
herd open digiper.test
```

---

## 📁 Folder Structure

### Backend Directory

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── PerkaraController.php        # CRUD perkaras
│   │   ├── LaporanController.php        # Reports
│   │   ├── HakimController.php          # Master hakim
│   │   ├── MasterDataController.php     # Master data endpoints
│   │   └── DashboardController.php      # Dashboard stats
│   ├── Requests/
│   │   ├── StorePerkaraRequest.php      # Validation rules
│   │   ├── UpdatePerkaraRequest.php
│   │   └── ImportPerkaraRequest.php
│   └── Resources/
│       ├── PerkaraResource.php          # API response format
│       └── HakimResource.php
├── Models/
│   ├── Perkara.php                      # Case model
│   ├── Hakim.php                        # Judge model
│   ├── Pejabat.php                      # Official model
│   ├── JenisPerkara.php                 # Case type master
│   ├── KomponenBiaya.php                # Cost component master
│   ├── DetailBiayaPerkara.php           # Cost breakdown per case
│   ├── DistribusiHonor.php              # Honor distribution
│   └── User.php
├── Imports/
│   └── PerkaraImport.php                # Excel import logic
├── Services/
│   ├── PerkaraService.php               # Business logic
│   ├── LaporanService.php               # Report generation
│   ├── DistribusiService.php            # Honor distribution logic
│   └── ImportService.php                # Import orchestration
├── Jobs/
│   ├── ProcessPerkaraImport.php         # Queue job for import
│   └── GenerateLaporan.php
├── Events/
│   ├── PerkaraImported.php
│   └── PerkaraUpdated.php
├── Listeners/
│   └── UpdateDistribusiOnPerkaraChange.php
├── Mail/
│   └── ImportCompleted.php              # Email notification
├── Exceptions/
│   ├── ImportException.php
│   └── ValidationException.php
└── Policies/
    └── PerkaraPolicy.php                # Authorization
```

### Database Directory

```
database/
├── migrations/
│   ├── 2026_05_21_create_perkaras_table.php
│   ├── 2026_05_21_create_hakims_table.php
│   ├── 2026_05_21_create_detail_biaya_perkaras_table.php
│   └── ... (more migrations)
├── factories/
│   ├── PerkaraFactory.php
│   └── HakimFactory.php
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── PerkaraSeeder.php
│   ├── HakimSeeder.php
│   └── ... (more seeders)
```

---

## 🗄️ Database & Migrations

### Key Tables & Relationships

#### 1. perkaras
```sql
CREATE TABLE perkaras (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    no_registrasi VARCHAR(50) UNIQUE NOT NULL,
    tanggal_masuk DATE NOT NULL,
    tanggal_putus DATE NOT NULL,
    kamar VARCHAR(50) NOT NULL,  -- TUN, Perdata, Agama, etc
    jenis_perkara_id BIGINT UNSIGNED,
    nama_p1 VARCHAR(255),
    nama_p2 VARCHAR(255),
    nama_p3 VARCHAR(255),
    nama_p4 VARCHAR(255),
    nama_p5 VARCHAR(255),
    hakim_p1_id BIGINT UNSIGNED,
    hakim_p2_id BIGINT UNSIGNED,
    hakim_p3_id BIGINT UNSIGNED,
    hakim_pemilah_id BIGINT UNSIGNED NULLABLE,
    tim_kepaniteraan_id BIGINT UNSIGNED NULLABLE,
    amar TEXT,
    biaya DECIMAL(12, 2) DEFAULT 0,
    status_biaya ENUM('belum_kena', 'kena') DEFAULT 'belum_kena',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    FOREIGN KEY (jenis_perkara_id) REFERENCES jenis_perkaras(id),
    FOREIGN KEY (hakim_p1_id) REFERENCES hakims(id),
    FOREIGN KEY (hakim_p2_id) REFERENCES hakims(id),
    FOREIGN KEY (hakim_p3_id) REFERENCES hakims(id),
    FOREIGN KEY (hakim_pemilah_id) REFERENCES hakims(id),
    FOREIGN KEY (tim_kepaniteraan_id) REFERENCES tim_kepaniteraanss(id),
    INDEX idx_status_biaya (status_biaya),
    INDEX idx_jenis (jenis_perkara_id),
    INDEX idx_tanggal (tanggal_masuk, tanggal_putus)
);
```

#### 2. hakims
```sql
CREATE TABLE hakims (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    nip VARCHAR(20) UNIQUE NOT NULL,
    pangkat ENUM('Ketua', 'Wakil Ketua', 'Ketua Kamar', 'Anggota') DEFAULT 'Anggota',
    urutan_seniority INT DEFAULT 999,
    status_aktif BOOLEAN DEFAULT true,
    tanggal_mulai DATE,
    tanggal_selesai DATE NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULLABLE,
    
    UNIQUE(nip),
    INDEX idx_status (status_aktif),
    INDEX idx_urutan (urutan_seniority)
);
```

#### 3. detail_biaya_perkaras
```sql
CREATE TABLE detail_biaya_perkaras (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    perkara_id BIGINT UNSIGNED NOT NULL,
    komponen_biaya_id BIGINT UNSIGNED NOT NULL,
    nominal DECIMAL(12, 2) NOT NULL,
    persentase DECIMAL(5, 2) NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (perkara_id) REFERENCES perkaras(id) ON DELETE CASCADE,
    FOREIGN KEY (komponen_biaya_id) REFERENCES komponen_biayas(id),
    INDEX idx_perkara (perkara_id)
);
```

#### 4. distribusi_honors
```sql
CREATE TABLE distribusi_honors (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    perkara_id BIGINT UNSIGNED NOT NULL,
    hakim_id BIGINT UNSIGNED,
    role ENUM('operator', 'panitera_pengganti', 'majelis', 'ketua_majelis', 'hakim_anggota') NOT NULL,
    nominal DECIMAL(12, 2) NOT NULL,
    persentase DECIMAL(5, 2),
    keterangan VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (perkara_id) REFERENCES perkaras(id) ON DELETE CASCADE,
    FOREIGN KEY (hakim_id) REFERENCES hakims(id) ON DELETE SET NULL,
    INDEX idx_perkara (perkara_id),
    INDEX idx_hakim (hakim_id)
);
```

---

## 🎯 Model & Relationships

### 1. Perkara Model

**File:** `app/Models/Perkara.php`

```php
class Perkara extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'no_registrasi', 'tanggal_masuk', 'tanggal_putus', 'kamar',
        'jenis_perkara_id', 'nama_p1', 'nama_p2', 'nama_p3', 'nama_p4', 'nama_p5',
        'hakim_p1_id', 'hakim_p2_id', 'hakim_p3_id', 'hakim_pemilah_id',
        'tim_kepaniteraan_id', 'amar', 'biaya', 'status_biaya'
    ];
    
    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_putus' => 'date',
        'biaya' => 'decimal:2',
    ];
    
    // Relationships
    public function jenisPerkara() {
        return $this->belongsTo(JenisPerkara::class);
    }
    
    public function hakimP1() {
        return $this->belongsTo(Hakim::class, 'hakim_p1_id');
    }
    
    public function hakimP2() {
        return $this->belongsTo(Hakim::class, 'hakim_p2_id');
    }
    
    public function hakimP3() {
        return $this->belongsTo(Hakim::class, 'hakim_p3_id');
    }
    
    public function detailBiaya() {
        return $this->hasMany(DetailBiayaPerkara::class);
    }
    
    public function distribusiHonor() {
        return $this->hasMany(DistribusiHonor::class);
    }
    
    // Methods
    public function getUsiaPerkara(): int {
        return abs($this->tanggal_masuk->diffInDays($this->tanggal_putus));
    }
    
    public function isValid(): bool {
        return $this->getUsiaPerkara() >= 90;
    }
    
    public function scopeValid($query) {
        return $query->whereRaw('ABS(DATEDIFF(tanggal_putus, tanggal_masuk)) >= 90');
    }
    
    public function scopeByJenis($query, $jenisPerkara) {
        return $query->where('jenis_perkara_id', $jenisPerkara);
    }
}
```

### 2. Hakim Model

```php
class Hakim extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'nama', 'nip', 'pangkat', 'urutan_seniority',
        'status_aktif', 'tanggal_mulai', 'tanggal_selesai'
    ];
    
    protected $casts = [
        'status_aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
    
    // Relationships
    public function perkarasAsP1() {
        return $this->hasMany(Perkara::class, 'hakim_p1_id');
    }
    
    public function distribusiHonor() {
        return $this->hasMany(DistribusiHonor::class);
    }
    
    // Scopes
    public function scopeActive($query) {
        return $query->where('status_aktif', true);
    }
    
    public function scopeOrderBySeniority($query) {
        return $query->orderBy('urutan_seniority');
    }
}
```

### 3. Other Models

Create models for:
- `JenisPerkara` - Case type master
- `KomponenBiaya` - Cost component master
- `DetailBiayaPerkara` - Cost breakdown
- `DistribusiHonor` - Honor distribution
- `Pejabat` - Official master
- `TimKepaniteraan` - Team grouping

---

## 🎮 Controllers & Routes

### 1. PerkaraController

**File:** `app/Http/Controllers/PerkaraController.php`

```php
class PerkaraController extends Controller
{
    public function __construct(
        protected PerkaraService $service
    ) {}
    
    // Dashboard
    public function index(Request $request)
    {
        $perkaras = Perkara::query()
            ->with('jenisPerkara')
            ->when($request->kamar, fn($q) => $q->where('kamar', $request->kamar))
            ->when($request->jenis, fn($q) => $q->where('jenis_perkara_id', $request->jenis))
            ->paginate(10);
        
        $stats = [
            'total' => Perkara::count(),
            'valid' => Perkara::valid()->count(),
            'totalBiaya' => Perkara::sum('biaya'),
            'hakimCount' => Hakim::active()->count(),
        ];
        
        return view('perkaras.index', compact('perkaras', 'stats'));
    }
    
    // Upload form
    public function create()
    {
        return view('perkaras.create');
    }
    
    // Process upload
    public function store(ImportPerkaraRequest $request)
    {
        try {
            $file = $request->file('file');
            
            $result = $this->service->importFromExcel($file);
            
            if ($result['success']) {
                return redirect()->route('perkaras.index')
                    ->with('success', "Imported {$result['count']} records");
            }
            
            return back()->with('error', $result['message']);
            
        } catch (\Exception $e) {
            Log::error('Import failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
    
    // Detail view
    public function show(Perkara $perkara)
    {
        $perkara->load('jenisPerkara', 'detailBiaya.komponen', 'distribusiHonor');
        
        return view('perkaras.show', compact('perkara'));
    }
    
    // Edit form
    public function edit(Perkara $perkara)
    {
        return view('perkaras.edit', compact('perkara'));
    }
    
    // Update
    public function update(UpdatePerkaraRequest $request, Perkara $perkara)
    {
        $perkara->update($request->validated());
        
        return redirect()->route('perkaras.show', $perkara)
            ->with('success', 'Perkara updated');
    }
    
    // Delete
    public function destroy(Perkara $perkara)
    {
        $perkara->delete();
        
        return redirect()->route('perkaras.index')
            ->with('success', 'Perkara deleted');
    }
}
```

### 2. Routes Configuration

**File:** `routes/web.php`

```php
use App\Http\Controllers\{
    PerkaraController,
    LaporanController,
    HakimController,
    MasterDataController,
};

Route::middleware('auth')->group(function () {
    // Redirect home to perkaras.create (upload)
    Route::redirect('/', '/perkaras/create');
    
    // Perkaras (main resource)
    Route::resource('perkaras', PerkaraController::class);
    
    // Laporan (reports)
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
    Route::post('laporan/cetak', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('laporan/export/{format}', [LaporanController::class, 'export'])->name('laporan.export');
    
    // Master Data
    Route::resource('master-data/hakims', HakimController::class);
    Route::resource('master-data/pejabat', MasterDataController::class);
    Route::resource('master-data/tarif', MasterDataController::class);
    
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('recap/total', [RekapController::class, 'total'])->name('recap.total');
});

// Auth routes (default Laravel)
Auth::routes();
```

---

## 💼 Services & Business Logic

### 1. PerkaraService

**File:** `app/Services/PerkaraService.php`

```php
class PerkaraService
{
    public function __construct(
        protected ImportService $importService,
        protected DistribusiService $distribusiService
    ) {}
    
    public function importFromExcel($file)
    {
        $path = $file->store('imports', 'local');
        
        try {
            $data = Excel::toArray(new PerkaraImport(), $path);
            
            $count = 0;
            foreach ($data[0] as $row) {
                $perkara = $this->createFromRow($row);
                
                if ($perkara->isValid()) {
                    $perkara->status_biaya = 'kena';
                    
                    // Apply tariff
                    $this->applyTariff($perkara);
                    
                    // Split biaya ke komponen
                    $this->splitBiaya($perkara);
                    
                    // Distribute honor
                    $this->distribusiService->distribute($perkara);
                    
                    $count++;
                }
            }
            
            return ['success' => true, 'count' => $count];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function createFromRow($row): Perkara
    {
        // Fuzzy match hakim dari nama
        $hakim1 = Hakim::fuzzyMatch($row['nama_p1'])->first();
        
        return Perkara::create([
            'no_registrasi' => $row['no_registrasi'],
            'tanggal_masuk' => $row['tanggal_masuk'],
            'tanggal_putus' => $row['tanggal_putus'],
            'kamar' => $row['kamar'],
            'jenis_perkara_id' => $this->resolveJenis($row['jenis']),
            'nama_p1' => $row['nama_p1'],
            'hakim_p1_id' => $hakim1?->id,
            'amar' => $row['amar'],
        ]);
    }
    
    private function applyTariff(Perkara $perkara)
    {
        $tarif = TarifBiaya::where('jenis_perkara_id', $perkara->jenis_perkara_id)
            ->first();
        
        $perkara->biaya = $tarif->tarif_normal ?? 0;
        $perkara->save();
    }
    
    private function splitBiaya(Perkara $perkara)
    {
        $komponens = KomponenBiaya::all();
        $totalFixed = 0;
        
        // Calculate fixed components
        foreach ($komponens->where('tipe', 'fixed') as $komponen) {
            DetailBiayaPerkara::create([
                'perkara_id' => $perkara->id,
                'komponen_biaya_id' => $komponen->id,
                'nominal' => $komponen->nilai,
            ]);
            $totalFixed += $komponen->nilai;
        }
        
        // Calculate percentage components
        $sisaBiaya = $perkara->biaya - $totalFixed;
        foreach ($komponens->where('tipe', 'percent') as $komponen) {
            $nominal = $sisaBiaya * ($komponen->nilai / 100);
            
            DetailBiayaPerkara::create([
                'perkara_id' => $perkara->id,
                'komponen_biaya_id' => $komponen->id,
                'nominal' => $nominal,
                'persentase' => $komponen->nilai,
            ]);
        }
    }
}
```

### 2. DistribusiService

```php
class DistribusiService
{
    public function distribute(Perkara $perkara)
    {
        $biaya = $perkara->biaya;
        
        // Operator: 5%
        DistribusiHonor::create([
            'perkara_id' => $perkara->id,
            'role' => 'operator',
            'nominal' => $biaya * 0.05,
            'persentase' => 5,
        ]);
        
        // Panitera Pengganti: 10%
        DistribusiHonor::create([
            'perkara_id' => $perkara->id,
            'role' => 'panitera_pengganti',
            'nominal' => $biaya * 0.10,
            'persentase' => 10,
        ]);
        
        // Majelis Hakim: 75% (split ke P1, P2, P3)
        $sisaBiaya = $biaya * 0.75;
        
        if ($perkara->hakim_p1_id) {
            DistribusiHonor::create([
                'perkara_id' => $perkara->id,
                'hakim_id' => $perkara->hakim_p1_id,
                'role' => 'ketua_majelis',
                'nominal' => $sisaBiaya * 0.35,
                'persentase' => 35,
            ]);
        }
        
        if ($perkara->hakim_p2_id) {
            DistribusiHonor::create([
                'perkara_id' => $perkara->id,
                'hakim_id' => $perkara->hakim_p2_id,
                'role' => 'hakim_anggota',
                'nominal' => $sisaBiaya * 0.25,
                'persentase' => 25,
            ]);
        }
        
        if ($perkara->hakim_p3_id) {
            DistribusiHonor::create([
                'perkara_id' => $perkara->id,
                'hakim_id' => $perkara->hakim_p3_id,
                'role' => 'hakim_anggota',
                'nominal' => $sisaBiaya * 0.25,
                'persentase' => 25,
            ]);
        }
    }
    
    public function getHakimHonorTotal($hakimId)
    {
        return DistribusiHonor::where('hakim_id', $hakimId)
            ->sum('nominal');
    }
}
```

---

## 📤 Excel Import

### PerkaraImport Class

**File:** `app/Imports/PerkaraImport.php`

```php
class PerkaraImport implements ToModel
{
    private $headers = [];
    private $row = 0;
    
    public function headingRow(): int
    {
        return 1;
    }
    
    public function map($row): ?Perkara
    {
        // Store headers on first row
        if (empty($this->headers)) {
            $this->headers = array_keys($row);
        }
        
        // Skip if no registrasi
        if (empty($row['no_registrasi'] ?? null)) {
            return null;
        }
        
        // Parse dates
        $tanggalMasuk = $this->parseDate($row['tanggal_masuk'] ?? null);
        $tanggalPutus = $this->parseDate($row['tanggal_putus'] ?? null);
        
        if (!$tanggalMasuk || !$tanggalPutus) {
            throw new \Exception("Invalid date format in row {$this->row}");
        }
        
        return new Perkara([
            'no_registrasi' => trim($row['no_registrasi']),
            'tanggal_masuk' => $tanggalMasuk,
            'tanggal_putus' => $tanggalPutus,
            'kamar' => $row['kamar'] ?? 'TUN',
            'nama_p1' => $row['nama_p1'] ?? null,
            'nama_p2' => $row['nama_p2'] ?? null,
            'nama_p3' => $row['nama_p3'] ?? null,
            'amar' => $row['amar'] ?? null,
        ]);
    }
    
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            // Try Carbon::parse for multiple formats
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
```

---

## ✓ Validation & Error Handling

### 1. Form Requests

**File:** `app/Http/Requests/ImportPerkaraRequest.php`

```php
class ImportPerkaraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,xlsm,xlsb|max:25600',
        ];
    }
    
    public function messages(): array
    {
        return [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls, .xlsm, .xlsb)',
            'file.max' => 'File tidak boleh lebih dari 25 MB',
        ];
    }
}
```

### 2. Custom Exception Handling

**File:** `app/Exceptions/ImportException.php`

```php
class ImportException extends Exception
{
    public function __construct($message, $rows = [])
    {
        parent::__construct($message);
        $this->rows = $rows;
    }
}
```

**In Handler:**
```php
public function render($request, Throwable $exception)
{
    if ($exception instanceof ImportException) {
        return response()->view('errors.import', [
            'message' => $exception->getMessage(),
            'rows' => $exception->rows,
        ], 422);
    }
    
    return parent::render($request, $exception);
}
```

---

## 🧪 Testing

### Unit Tests

**File:** `tests/Unit/PerkaraServiceTest.php`

```php
class PerkaraServiceTest extends TestCase
{
    protected PerkaraService $service;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->service = app(PerkaraService::class);
    }
    
    public function test_perkara_usia_calculation()
    {
        $perkara = Perkara::factory()->create([
            'tanggal_masuk' => now()->subDays(405),
            'tanggal_putus' => now(),
        ]);
        
        $this->assertEquals(405, $perkara->getUsiaPerkara());
    }
    
    public function test_perkara_is_valid()
    {
        $valid = Perkara::factory()->create([
            'tanggal_masuk' => now()->subDays(100),
            'tanggal_putus' => now(),
        ]);
        
        $invalid = Perkara::factory()->create([
            'tanggal_masuk' => now()->subDays(30),
            'tanggal_putus' => now(),
        ]);
        
        $this->assertTrue($valid->isValid());
        $this->assertFalse($invalid->isValid());
    }
}
```

### Feature Tests

**File:** `tests/Feature/ImportPerkaraTest.php`

```php
class ImportPerkaraTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_import_excel_file()
    {
        $file = UploadedFile::fake()->create('perkaras.xlsx');
        
        $response = $this->post(route('perkaras.store'), [
            'file' => $file,
        ]);
        
        $response->assertRedirect(route('perkaras.index'));
        $response->assertSessionHas('success');
    }
    
    public function test_import_validates_file_type()
    {
        $file = UploadedFile::fake()->create('perkaras.txt');
        
        $response = $this->post(route('perkaras.store'), [
            'file' => $file,
        ]);
        
        $response->assertSessionHasErrors('file');
    }
}
```

---

## 📋 Best Practices

### 1. Eloquent Query Optimization

```php
// ✅ Good - Use select() to limit columns
Perkara::select('id', 'no_registrasi', 'biaya')
    ->with('jenisPerkara:id,nama')
    ->get();

// ✅ Good - Use eager loading
$perkaras = Perkara::with('jenisPerkara', 'detailBiaya')->get();

// ❌ Bad - N+1 query problem
foreach ($perkaras as $perkara) {
    echo $perkara->jenisPerkara->nama; // Extra query per iteration
}
```

### 2. Caching

```php
// Cache query results
$stats = Cache::remember('perkara_stats', 3600, function () {
    return [
        'total' => Perkara::count(),
        'valid' => Perkara::valid()->count(),
        'totalBiaya' => Perkara::sum('biaya'),
    ];
});

// Invalidate cache on model change
public static function booted()
{
    static::created(fn() => Cache::forget('perkara_stats'));
    static::updated(fn() => Cache::forget('perkara_stats'));
    static::deleted(fn() => Cache::forget('perkara_stats'));
}
```

### 3. Model Observers

```php
// app/Observers/PerkaraObserver.php
class PerkaraObserver
{
    public function created(Perkara $perkara)
    {
        // Auto-create detail_biaya & distribusi_honor
        app(PerkaraService::class)->splitBiaya($perkara);
        app(DistribusiService::class)->distribute($perkara);
    }
    
    public function updated(Perkara $perkara)
    {
        // If biaya changed, recalculate
        if ($perkara->isDirty('biaya')) {
            $perkara->detailBiaya()->delete();
            app(PerkaraService::class)->splitBiaya($perkara);
        }
    }
}

// Register in AppServiceProvider
public function boot()
{
    Perkara::observe(PerkaraObserver::class);
}
```

### 4. Database Indexing

```php
// In migration
Schema::create('perkaras', function (Blueprint $table) {
    $table->id();
    // ...
    $table->index('status_biaya');
    $table->index('jenis_perkara_id');
    $table->index(['tanggal_masuk', 'tanggal_putus']);
    $table->fullText(['no_registrasi', 'amar']);
});
```

### 5. Soft Deletes

```php
// For audit trail
use SoftDeletes;

// Query
Perkara::withTrashed()->get();    // Include deleted
Perkara::onlyTrashed()->get();    // Only deleted
Perkara::get();                   // Exclude deleted (default)
```

---

## 📚 Code Organization

### Service vs Repository vs Controller

```
Controller      → HTTP request/response handling
Service         → Business logic (orchestration)
Repository      → Data access (Eloquent queries)
Model           → Business rules, relationships
```

### Example Flow

```
HTTP Request
    ↓
PerkaraController@store
    ↓
ImportPerkaraRequest (validation)
    ↓
PerkaraService@importFromExcel (orchestration)
    ↓
Perkara::create() / Hakim::fuzzyMatch()
    ↓
PerkaraObserver@created (auto-side-effects)
    ↓
HTTP Response
```

---

## 🔗 Related Documentation

- See [BACKEND_API_SCHEMA.md](BACKEND_API_SCHEMA.md) for detailed API specs
- See [DOKUMENTASI_SISTEM.md](DOKUMENTASI_SISTEM.md) for system architecture
- See [FRONTEND_DEVELOPMENT_GUIDE.md](FRONTEND_DEVELOPMENT_GUIDE.md) for frontend integration

---

**Last Updated:** Mei 2026  
**Status:** Ready for Implementation
