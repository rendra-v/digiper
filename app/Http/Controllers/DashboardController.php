<?php

namespace App\Http\Controllers;

use App\Models\ExcelFile;
use App\Models\Perkara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DashboardController extends Controller
{
    public function index()
    {
        $excelFiles = ExcelFile::orderBy('created_at', 'desc')->get();
        $currentFileId = Session::get('current_file_id');

        return view('dashboard', [
            'excelFiles' => $excelFiles,
            'currentFileId' => $currentFileId,
        ]);
    }

    public function uploadWithPeriod(Request $request)
    {
        try {
            // Validate file and period
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:102400',
                'period' => 'required|string|max:100',
            ], [
                'file.required' => 'File harus diupload',
                'file.mimes' => 'Format file harus Excel (.xlsx, .xls, .csv)',
                'file.max' => 'Ukuran file maksimal 100MB',
                'period.required' => 'Periode harus diisi',
            ]);

            $file = $request->file('file');
            $period = $request->input('period');

            // Store uploaded file
            $uploadDir = storage_path('app/uploads');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
            $fullPath = $uploadDir.'/'.$filename;
            $file->move($uploadDir, $filename);

            // Save to database
            $excelFile = ExcelFile::create([
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $fullPath,
                'period' => $period,
            ]);

            // Load into session
            Session::put('current_file_id', $excelFile->id);
            $this->loadFileToSession($excelFile);

            \Log::info('File uploaded with period', [
                'file_id' => $excelFile->id,
                'period' => $period,
                'filename' => $file->getClientOriginalName(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload untuk periode '.$period,
                'file_id' => $excelFile->id,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: '.collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Upload error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 400);
        }
    }

    public function viewFile($id)
    {
        try {
            $excelFile = ExcelFile::findOrFail($id);

            if (! file_exists($excelFile->file_path)) {
                return redirect('/dashboard')->with('error', 'File tidak ditemukan');
            }

            Session::put('current_file_id', $excelFile->id);
            $this->loadFileToSession($excelFile);

            return redirect('/data-print');

        } catch (\Exception $e) {
            return redirect('/dashboard')->with('error', 'File tidak dapat diakses');
        }
    }

    private function loadFileToSession($excelFile)
    {
        try {
            $reader = IOFactory::createReaderForFile($excelFile->file_path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($excelFile->file_path);

            $sheetNames = $spreadsheet->getSheetNames();

            Session::put('excel_file_name', $excelFile->original_filename);
            Session::put('excel_file_path', $excelFile->file_path);
            Session::put('excel_sheets', $sheetNames);
            Session::put('excel_period', $excelFile->period);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

        } catch (\Exception $e) {
            \Log::error('Error loading file to session', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function upload(Request $request)
    {
        try {
            // Validate file
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:102400', // 100MB max
            ], [
                'file.required' => 'File harus diupload',
                'file.mimes' => 'Format file harus Excel (.xlsx, .xls, .csv)',
                'file.max' => 'Ukuran file maksimal 100MB',
            ]);

            $file = $request->file('file');

            // Log file info
            \Log::info('Uploading file', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);

            // Store uploaded file temporarily in app/uploads directory
            $uploadDir = storage_path('app/uploads');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename and save directly
            $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
            $fullPath = $uploadDir.'/'.$filename;
            $file->move($uploadDir, $filename);

            // Load spreadsheet with optimizations from the uploaded file
            $reader = IOFactory::createReaderForFile($fullPath);
            $reader->setReadDataOnly(true); // Only read data, skip formatting
            $spreadsheet = $reader->load($fullPath);

            // Get all sheet names
            $sheetNames = $spreadsheet->getSheetNames();
            \Log::info('Available sheets', ['sheets' => $sheetNames]);

            // Try to find "Data Print" sheet, otherwise use first sheet
            $targetSheetName = 'Data Print';
            if (! in_array($targetSheetName, $sheetNames)) {
                $targetSheetName = $sheetNames[0]; // Use first sheet if "Data Print" not found
            }

            $worksheet = $spreadsheet->getSheetByName($targetSheetName);

            $data = [];
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            \Log::info('Excel dimensions', [
                'sheet' => $targetSheetName,
                'rows' => $highestRow,
                'columns' => $highestColumn,
            ]);

            // Limit rows to prevent memory issues (max 50000 rows)
            $maxRows = min($highestRow, 50000);

            // Find header row (scan from row 1 to 10 to find actual headers)
            $headerRow = 1;
            for ($row = 1; $row <= min(10, $highestRow); $row++) {
                $cellValue = $worksheet->getCell('A'.$row)->getValue();
                // If this row starts with "No", "Nomor", or similar, it's the header
                if (strtoupper($cellValue) === 'NO' || stripos($cellValue, 'nomor') !== false) {
                    $headerRow = $row;
                    break;
                }
            }

            \Log::info('Header row detected', ['row' => $headerRow]);

            // Get header row
            $headers = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cell = $worksheet->getCell($col.$headerRow);
                $headers[$col] = trim($cell->getValue() ?: $col);
            }

            // Read data rows (starting after header)
            for ($row = $headerRow + 1; $row <= $maxRows; $row++) {
                $rowData = [];
                $hasData = false;

                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cell = $worksheet->getCell($col.$row);
                    $value = $cell->getValue();

                    // Use header names as keys
                    $key = $headers[$col] ?: $col;
                    $rowData[$key] = $value;

                    if ($value !== null && $value !== '') {
                        $hasData = true;
                    }
                }

                // Only add non-empty rows
                if ($hasData) {
                    $data[] = $rowData;
                }
            }

            // Store both data and sheet info in session
            Session::put('excel_data', $data);
            Session::put('excel_sheets', $sheetNames);
            Session::put('excel_current_sheet', $targetSheetName);
            Session::put('excel_file_name', $file->getClientOriginalName());
            Session::put('excel_file_path', $fullPath);

            // Cleanup
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            \Log::info('Upload successful', [
                'rows_imported' => count($data),
                'total_rows_in_file' => $highestRow,
                'sheet' => $targetSheetName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload! Total: '.count($data).' baris',
                'count' => count($data),
            ]);

        } catch (ValidationException $e) {
            \Log::warning('Validation error', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: '.collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Upload error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 400);
        }
    }

    public function clear()
    {
        Session::forget('excel_data');
        Session::forget('excel_sheets');
        Session::forget('excel_current_sheet');
        Session::forget('excel_file_name');
        Session::forget('excel_file_path');

        return response()->json(['success' => true]);
    }

    public function dataPrint()
    {
        try {
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            // DEBUG: Log session values
            \Log::info('dataPrint() called', [
                'filePath' => $filePath,
                'fileName' => $fileName,
                'fileExists' => $filePath ? file_exists($filePath) : 'no filePath',
                'allSessionKeys' => array_keys(Session::all()),
            ]);

            if (! $filePath || ! file_exists($filePath)) {
                return view('data-print', [
                    'categories' => [],
                    'fileName' => null,
                    'error' => 'File tidak ditemukan. Silakan upload file terlebih dahulu.',
                ]);
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            if (! $spreadsheet->sheetNameExists('Data Print')) {
                return view('data-print', [
                    'categories' => [],
                    'fileName' => $fileName,
                    'error' => 'Sheet "Data Print" tidak ditemukan dalam file.',
                ]);
            }

            $worksheet = $spreadsheet->getSheetByName('Data Print');
            $categories = $this->parseDataPrintSheet($worksheet);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return view('data-print', [
                'categories' => $categories,
                'fileName' => $fileName,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in dataPrint', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return view('data-print', [
                'categories' => [],
                'fileName' => Session::get('excel_file_name'),
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function sheetCek()
    {
        try {
            $filePath = Session::get('excel_file_path');

            if (! $filePath || ! file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan',
                ], 404);
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            if (! $spreadsheet->sheetNameExists('cek')) {
                return view('sheet-cek', [
                    'data' => [],
                    'error' => 'Sheet "cek" tidak ditemukan',
                ]);
            }

            $worksheet = $spreadsheet->getSheetByName('cek');
            $data = [];
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            // Helper to filter formula strings
            $getCellValue = function ($cell) {
                $value = $cell->getValue();
                // Skip formula strings that appear as text (e.g., "=C6", "=D6")
                if (is_string($value) && strpos($value, '=') === 0) {
                    return null;
                }

                return $value;
            };

            // Find header row (contains "NO" anywhere)
            $headerRowNum = 1;
            for ($row = 1; $row <= min($highestRow, 10); $row++) {
                $rowHasNO = false;
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cellValue = $getCellValue($worksheet->getCell($col.$row));
                    if ($cellValue && strtoupper(trim($cellValue)) === 'NO') {
                        $rowHasNO = true;
                        break;
                    }
                }
                if ($rowHasNO) {
                    $headerRowNum = $row;
                    break;
                }
            }

            // Get headers
            $headers = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $headerValue = $getCellValue($worksheet->getCell($col.$headerRowNum));
                $headers[$col] = trim($headerValue ?: $col);
            }

            // Get data rows (starting after header row)
            $dataStartRow = $headerRowNum + 1;
            for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                $rowData = [];
                $hasData = false;

                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $value = $getCellValue($worksheet->getCell($col.$row));
                    $key = $headers[$col];
                    $rowData[$key] = $value;

                    if ($value !== null && $value !== '') {
                        $hasData = true;
                    }
                }

                if ($hasData) {
                    $data[] = $rowData;
                }
            }

            $spreadsheet->disconnectWorksheets();

            return view('sheet-cek', [
                'data' => $data,
                'headers' => $headers,
                'error' => null,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reading cek sheet', ['error' => $e->getMessage()]);

            return view('sheet-cek', [
                'data' => [],
                'error' => 'Error membaca sheet: '.$e->getMessage(),
            ]);
        }
    }

    private function parseDataPrintSheet($worksheet)
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumnLetter = $worksheet->getHighestColumn();

        \Log::info('parseDataPrintSheet started', [
            'rows' => $highestRow,
            'columns' => $highestColumnLetter,
        ]);

        // Helper to convert column index to letter (1=A, 26=Z, 27=AA, etc)
        $indexToColumn = function ($index) {
            $letter = '';
            while ($index > 0) {
                $index--;
                $letter = chr(65 + ($index % 26)).$letter;
                $index = intdiv($index, 26);
            }

            return $letter;
        };

        // Helper to convert column letter to index (A=1, Z=26, AA=27, etc)
        $columnToIndex = function ($col) {
            $index = 0;
            for ($i = 0; $i < strlen($col); $i++) {
                $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
            }

            return $index;
        };

        // Helper to get cell value and filter out formula strings like "=C6" and error values
        $getCellValue = function ($cell) {
            $value = $cell->getValue();

            // Skip formula strings that appear as text (e.g., "=C6", "=D6")
            if (is_string($value) && strpos($value, '=') === 0) {
                return null;
            }

            // Skip error values (#REF!, #VALUE!, etc)
            if (is_string($value) && strpos($value, '#') === 0) {
                return null;
            }

            return $value;
        };

        // Helper to check if row is a valid data row (not a TOTAL or section footer)
        $isValidDataRow = function ($rowData, $currentHeaders) {
            // Get first cell value (NO column)
            $firstCell = null;
            foreach ($currentHeaders as $col => $header) {
                if ($header === 'No' && isset($rowData[$header])) {
                    $firstCell = $rowData[$header];
                    break;
                }
            }

            if (! $firstCell) {
                return false; // No first cell means it's not a valid data row
            }

            $firstCellStr = trim((string) $firstCell);

            // Skip header rows (contains "No", "NO", "Number", etc)
            if (strtoupper($firstCellStr) === 'NO' || strtoupper($firstCellStr) === 'NUMBER') {
                return false;
            }

            // Skip section headers and footers (contains "PERKARA", "TOTAL", "DATA", "~")
            if (stripos($firstCellStr, 'PERKARA') !== false ||
                stripos($firstCellStr, 'TOTAL') !== false ||
                stripos($firstCellStr, 'DATA') !== false ||
                strpos($firstCellStr, '~') !== false) {
                return false;
            }

            // Skip if first cell is not numeric (should be a row number)
            if (! is_numeric($firstCellStr) && ! ctype_digit($firstCellStr)) {
                return false;
            }

            // Skip if all meaningful values are just "-" or empty
            $meaningfulCount = 0;
            foreach ($rowData as $value) {
                $val = trim((string) $value);
                if ($val !== '' && $val !== '-' && $val !== '~') {
                    $meaningfulCount++;
                }
            }

            return $meaningfulCount > 1; // At least 2 meaningful columns
        };

        $highestColumnIndex = $columnToIndex($highestColumnLetter);

        // Define categories dengan section headers
        $categoryDefinitions = [
            'DATA PERKARA KASASI PERDATA UMUM' => 'kasasi-pdt-umum',
            'DATA PERKARA PENINJAUAN KEMBALI PERDATA UMUM' => 'pk-pdt-umum',
            'DATA PERKARA KASASI PERDATA KHUSUS' => 'kasasi-pdt-khusus',
            'DATA PERKARA PENINJAUAN KEMBALI PERDATA KHUSUS' => 'pk-pdt-khusus',
            'DATA PERKARA KASASI  PERDATA AGAMA' => 'kasasi-pdt-agama',
            'DATA PERKARA PENINJAUAN KEMBALI  PERDATA AGAMA' => 'pk-pdt-agama',
            'DATA PERKARA KASASI  TATA USAHA NEGARA (K-TUN)' => 'kasasi-tun',
            'DATA PERKARA PERMOHONAN HAK UJI MATERIL (P-HUM)' => 'phum',
            'DATA PERKARA PERMOHONAN HAK UJI PENDAPAT (P-KHS)' => 'pkhs',
            'DATA PERKARA PENINJAUAN KEMBALI  TATA USAHA NEGARA (PK-TUN)' => 'pk-tun',
            'DATA PERKARA PENINJAUAN KEMBALI  PAJAK (PK-PJK)' => 'pk-pajak',
        ];

        // Initialize categories
        $categories = [];
        foreach ($categoryDefinitions as $title => $id) {
            $categories[$id] = [
                'id' => $id,
                'title' => $title,
                'data' => [],
                'count' => 0,
                'columns' => [],
                'total' => null, // Store total value from Excel
            ];
        }

        // Find section breaks and parse data
        $currentSection = null;
        $currentHeaderRow = null;
        $currentHeaders = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $firstCell = trim($worksheet->getCell('A'.$row)->getValue() ?? '');

            // Check if this is a section header
            $isSectionHeader = false;
            foreach ($categoryDefinitions as $sectionTitle => $sectionId) {
                if (stripos($firstCell, $sectionTitle) !== false) {
                    $currentSection = $sectionId;
                    $isSectionHeader = true;
                    \Log::info('Found section header', ['section' => $currentSection, 'row' => $row]);
                    break;
                }
            }

            if ($isSectionHeader) {
                $currentHeaderRow = null;

                continue;
            }

            // Check if this is a header row (contains "No" in first column)
            if ($currentSection && $firstCell === 'No' && $currentHeaderRow === null) {
                $currentHeaderRow = $row;
                $currentHeaders = [];

                // Iterate through all columns using numeric indices
                for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
                    $col = $indexToColumn($colIndex);
                    $header = trim($worksheet->getCell($col.$row)->getValue() ?? '');
                    $currentHeaders[$col] = $header ?: $col;
                }

                // DEBUG: Count actual headers
                $headerCount = 0;
                $headerList = [];
                foreach ($currentHeaders as $col => $headerName) {
                    if ($headerName && $headerName !== $col) {
                        $headerCount++;
                        $headerList[] = $headerName;
                    }
                }

                \Log::info('Found header row', [
                    'section' => $currentSection,
                    'row' => $row,
                    'total_headers' => $headerCount,
                    'sample_headers' => array_slice($headerList, 0, 10),
                ]);

                if ($currentSection) {
                    $categories[$currentSection]['columns'] = $currentHeaders;
                }

                continue;
            }

            // Parse data rows
            if ($currentSection && $currentHeaderRow !== null && $row > $currentHeaderRow) {
                $rowData = [];
                $hasData = false;

                // Iterate through all columns using numeric indices
                for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
                    $col = $indexToColumn($colIndex);
                    $value = $getCellValue($worksheet->getCell($col.$row));

                    if ($value !== null && $value !== '') {
                        $hasData = true;
                    }

                    $key = $currentHeaders[$col] ?? $col;
                    $rowData[$key] = $value;
                }

                // Check if this is a TOTAL row (store total value, don't include in data)
                if ($hasData) {
                    $firstCell = $rowData['No'] ?? null;
                    if ($firstCell && stripos(trim((string) $firstCell), 'TOTAL') !== false) {
                        // Extract total value from the second column (usually JUMLAH)
                        $secondCol = null;
                        $colIndex = 0;
                        foreach ($currentHeaders as $col => $header) {
                            $colIndex++;
                            if ($colIndex === 2) { // Usually second column has the total
                                $secondCol = $header;
                                break;
                            }
                        }

                        if ($secondCol && isset($rowData[$secondCol])) {
                            $totalValue = $rowData[$secondCol];
                            if ($totalValue !== null && $totalValue !== '') {
                                $categories[$currentSection]['total'] = $totalValue;
                                \Log::info('Found TOTAL row', [
                                    'section' => $currentSection,
                                    'row' => $row,
                                    'firstCell' => $firstCell,
                                    'total' => $totalValue,
                                    'secondCol' => $secondCol,
                                ]);
                            }
                        }

                        continue; // Skip adding TOTAL to data rows
                    }
                }

                // Validate if this is a real data row (not TOTAL, not all empty/dashes)
                if ($hasData && $isValidDataRow($rowData, $currentHeaders)) {
                    $categories[$currentSection]['data'][] = $rowData;
                    $categories[$currentSection]['count']++;

                    // Log first row for debugging
                    if ($categories[$currentSection]['count'] === 1) {
                        \Log::info('First data row parsed', [
                            'section' => $currentSection,
                            'total_columns' => count($rowData),
                            'sample_values' => array_slice($rowData, 0, 5),
                        ]);
                    }
                }
            }
        }

        \Log::info('parseDataPrintSheet completed', [
            'categories' => array_map(fn ($c) => ['id' => $c['id'], 'count' => $c['count']], array_values($categories)),
        ]);

        // Set total = count for categories that don't have an explicit total
        foreach ($categories as &$category) {
            if ($category['count'] > 0 && ($category['total'] === null || $category['total'] === '')) {
                $category['total'] = $category['count'];
            }
        }

        return array_values($categories);
    }

    private function parseDataPrintCategories($data)
    {
        // Struktur kategori perkara berdasarkan analisis file
        $categories = [
            [
                'id' => 'kasasi-pdt-umum',
                'name' => 'DATA PERKARA KASASI PERDATA UMUM',
                'key' => 'Kasasi PDT Umum',
            ],
            [
                'id' => 'pk-pdt-umum',
                'name' => 'DATA PERKARA PENINJAUAN KEMBALI PERDATA UMUM',
                'key' => 'PK PDT Umum',
            ],
            [
                'id' => 'kasasi-pdt-khusus',
                'name' => 'DATA PERKARA KASASI PERDATA KHUSUS',
                'key' => 'Kasasi PDT Khusus',
            ],
            [
                'id' => 'pk-pdt-khusus',
                'name' => 'DATA PERKARA PENINJAUAN KEMBALI PERDATA KHUSUS',
                'key' => 'PK PDT Khusus',
            ],
            [
                'id' => 'kasasi-pdt-agama',
                'name' => 'DATA PERKARA KASASI  PERDATA AGAMA',
                'key' => 'Kasasi PDT Agama',
            ],
            [
                'id' => 'pk-pdt-agama',
                'name' => 'DATA PERKARA PENINJAUAN KEMBALI  PERDATA AGAMA',
                'key' => 'PK PDT Agama',
            ],
            [
                'id' => 'kasasi-tun',
                'name' => 'DATA PERKARA KASASI  TATA USAHA NEGARA (K-TUN)',
                'key' => 'Kasasi TUN',
            ],
            [
                'id' => 'phum',
                'name' => 'DATA PERKARA PERMOHONAN HAK UJI MATERIL (P-HUM)',
                'key' => 'P-HUM',
            ],
            [
                'id' => 'pkhs',
                'name' => 'DATA PERKARA PERMOHONAN HAK UJI PENDAPAT (P-KHS)',
                'key' => 'P-KHS',
            ],
            [
                'id' => 'pk-tun',
                'name' => 'DATA PERKARA PENINJAUAN KEMBALI  TATA USAHA NEGARA (PK-TUN)',
                'key' => 'PK-TUN',
            ],
            [
                'id' => 'pk-pajak',
                'name' => 'DATA PERKARA PENINJAUAN KEMBALI  PAJAK (PK-PJK)',
                'key' => 'PK-PJK',
            ],
        ];

        // Format data dengan kategori
        foreach ($categories as &$category) {
            $category['data'] = [];
            $category['count'] = 0;
        }

        return $categories;
    }

    public function getSheet($sheetName)
    {
        try {
            $sheets = Session::get('excel_sheets', []);
            $filePath = Session::get('excel_file_path');

            if (! in_array($sheetName, $sheets)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sheet tidak ditemukan',
                ], 404);
            }

            if (! $filePath || ! file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan di storage',
                ], 404);
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getSheetByName($sheetName);

            $data = [];
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            // Find header row
            $headerRow = 1;
            for ($row = 1; $row <= min(10, $highestRow); $row++) {
                $cellValue = $worksheet->getCell('A'.$row)->getValue();
                if (strtoupper($cellValue) === 'NO' || stripos($cellValue, 'nomor') !== false) {
                    $headerRow = $row;
                    break;
                }
            }

            // Get header row
            $headers = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cell = $worksheet->getCell($col.$headerRow);
                $headers[$col] = trim($cell->getValue() ?: $col);
            }

            // Read data rows
            $maxRows = min($highestRow, 50000);
            for ($row = $headerRow + 1; $row <= $maxRows; $row++) {
                $rowData = [];
                $hasData = false;

                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cell = $worksheet->getCell($col.$row);
                    $value = $cell->getValue();
                    $key = $headers[$col] ?: $col;
                    $rowData[$key] = $value;

                    if ($value !== null && $value !== '') {
                        $hasData = true;
                    }
                }

                if ($hasData) {
                    $data[] = $rowData;
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return response()->json([
                'success' => true,
                'sheet' => $sheetName,
                'data' => $data,
                'count' => count($data),
            ]);

        } catch (\Exception $e) {
            \Log::error('Get sheet error', [
                'sheet' => $sheetName,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 400);
        }
    }

    public function deleteFile($id)
    {
        try {
            $excelFile = ExcelFile::findOrFail($id);

            // Delete file from storage
            if (file_exists($excelFile->file_path)) {
                unlink($excelFile->file_path);
            }

            // Delete from database
            $excelFile->delete();

            // If this was the current file, clear session
            if (Session::get('current_file_id') === $id) {
                Session::forget('current_file_id');
                Session::forget('excel_file_name');
                Session::forget('excel_file_path');
                Session::forget('excel_sheets');
                Session::forget('excel_period');
            }

            \Log::info('File deleted', [
                'file_id' => $id,
                'filename' => $excelFile->original_filename,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            \Log::error('Delete file error', [
                'file_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 400);
        }
    }

    public function renamePeriod(Request $request, $id)
    {
        try {
            $request->validate([
                'period' => 'required|string|max:100',
            ], [
                'period.required' => 'Periode harus diisi',
                'period.max' => 'Periode maksimal 100 karakter',
            ]);

            $excelFile = ExcelFile::findOrFail($id);
            $oldPeriod = $excelFile->period;
            $newPeriod = $request->input('period');

            $excelFile->update([
                'period' => $newPeriod,
            ]);

            // If this was the current file, update session
            if (Session::get('current_file_id') === $id) {
                Session::put('excel_period', $newPeriod);
            }

            \Log::info('Period renamed', [
                'file_id' => $id,
                'old_period' => $oldPeriod,
                'new_period' => $newPeriod,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil diubah',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: '.collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Rename period error', [
                'file_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 400);
        }
    }
}
