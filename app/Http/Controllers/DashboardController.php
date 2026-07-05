<?php

namespace App\Http\Controllers;

use App\Models\ExcelFile;
use App\Models\Perkara;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardController extends Controller
{
    private const APP_MAX_UPLOAD_KB = 512000; // 500MB

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
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $maxUploadKb = min(self::APP_MAX_UPLOAD_KB, $this->getPhpUploadLimitKb());

            $uploadedFile = $request->file('file');
            if ($uploadedFile instanceof UploadedFile && ! $uploadedFile->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->getUploadErrorMessage($uploadedFile),
                ], 422);
            }

            // Validate file and period
            $request->validate([
                'file' => 'required|file|extensions:xlsx,xls,xlsm,xlsb,csv|max:'.$maxUploadKb,
                'period' => 'required|string|max:100',
            ], [
                'file.required' => 'File harus diupload',
                'file.extensions' => 'Format file harus Excel (.xlsx, .xls, .xlsm, .xlsb, .csv)',
                'file.max' => 'Ukuran file melebihi batas upload server',
                'period.required' => 'Periode harus diisi',
            ]);

            $file = $request->file('file');
            $period = $request->input('period');

            // Store uploaded file
            $uploadDir = storage_path('app/uploads');
            if (! is_dir($uploadDir)) {
                File::ensureDirectoryExists($uploadDir);
            }

            $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
            $fullPath = $uploadDir.'/'.$filename;
            $storedPath = 'uploads/'.$filename;
            $file->move($uploadDir, $filename);

            // Save to database
            $excelFile = ExcelFile::create([
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
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
            $firstError = collect($e->errors())->flatten()->first();
            if (is_string($firstError) && Str::contains(Str::lower($firstError), 'failed to upload')) {
                $firstError = 'Upload gagal di level server. Pastikan ukuran file tidak melebihi batas PHP (upload_max_filesize/post_max_size).';
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: '.$firstError,
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

            $resolvedPath = $this->resolveExcelFilePath($excelFile->file_path);
            if (! $resolvedPath) {
                return redirect('/dashboard')->with('error', 'File tidak ditemukan');
            }

            // Hapus cache lama jika file berbeda
            $oldPath = Session::get('excel_file_path', '');
            if ($oldPath !== $resolvedPath) {
                $this->invalidateSessionCache();
            }

            Session::put('current_file_id', $excelFile->id);
            Session::put('excel_file_name', $excelFile->original_filename);
            Session::put('excel_file_path', $resolvedPath);
            Session::put('excel_period', $excelFile->period);

            return redirect('/data-print');
        } catch (\Exception $e) {
            return redirect('/dashboard')->with('error', 'File tidak dapat diakses');
        }
    }

    private function loadFileToSession($excelFile)
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = $this->resolveExcelFilePath($excelFile->file_path);
            if (! $filePath) {
                throw new \RuntimeException('File tidak ditemukan di storage aplikasi.');
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $sheetNames = $spreadsheet->getSheetNames();

            // Hapus cache lama sebelum set file baru
            $this->invalidateSessionCache();

            Session::put('excel_file_name', $excelFile->original_filename);
            Session::put('excel_file_path', $filePath);
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
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $maxUploadKb = min(self::APP_MAX_UPLOAD_KB, $this->getPhpUploadLimitKb());

            $uploadedFile = $request->file('file');
            if ($uploadedFile instanceof UploadedFile && ! $uploadedFile->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->getUploadErrorMessage($uploadedFile),
                ], 422);
            }

            // Validate file
            $request->validate([
                'file' => 'required|file|extensions:xlsx,xls,xlsm,xlsb,csv|max:'.$maxUploadKb,
            ], [
                'file.required' => 'File harus diupload',
                'file.extensions' => 'Format file harus Excel (.xlsx, .xls, .xlsm, .xlsb, .csv)',
                'file.max' => 'Ukuran file melebihi batas upload server',
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
                File::ensureDirectoryExists($uploadDir);
            }

            // Generate unique filename and save directly
            $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
            $fullPath = $uploadDir.'/'.$filename;
            $storedPath = 'uploads/'.$filename;
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

            // Save to database
            $excelFile = ExcelFile::create([
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'period' => '',
            ]);

            // Store both data and sheet info in session
            Session::put('current_file_id', $excelFile->id);
            Session::put('excel_data', $data);
            Session::put('excel_sheets', $sheetNames);
            Session::put('excel_current_sheet', $targetSheetName);
            Session::put('excel_file_name', $file->getClientOriginalName());
            Session::put('excel_file_path', $fullPath);
            Session::put('excel_period', '');

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
        $this->invalidateSessionCache();

        Session::forget('excel_data');
        Session::forget('excel_sheets');
        Session::forget('excel_current_sheet');
        Session::forget('excel_file_name');
        Session::forget('excel_file_path');
        Session::forget('current_file_id');
        Session::forget('excel_period');

        return response()->json(['success' => true]);
    }

    public function dataPrint()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);

            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            \Log::info('dataPrint() called', [
                'filePath' => $filePath,
                'fileName' => $fileName,
                'fileExists' => $filePath ? file_exists($filePath) : 'no filePath',
            ]);

            if (! $filePath || ! file_exists($filePath)) {
                return view('data-print', [
                    'categories' => [],
                    'fileName' => null,
                    'error' => 'File tidak ditemukan. Silakan upload file terlebih dahulu.',
                ]);
            }

            // Ambil dari cache session jika tersedia
            $cacheKey = $this->getCacheKey($filePath, 'data_print');
            $cached = Session::get($cacheKey);

            if ($cached !== null) {
                \Log::info('dataPrint() - loaded from session cache');

                return view('data-print', [
                    'categories' => $cached['categories'],
                    'fileName' => $fileName,
                    'error' => null,
                ]);
            }

            \Log::info('dataPrint() - loading from file (no cache)');
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            // Hanya load sheet "Data Print" saja, jangan load seluruh workbook
            if (method_exists($reader, 'setLoadSheetsOnly')) {
                $reader->setLoadSheetsOnly(['Data Print']);
            }
            $spreadsheet = $reader->load($filePath);

            if (! $spreadsheet->sheetNameExists('Data Print')) {
                $spreadsheet->disconnectWorksheets();

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

            // Simpan ke session cache
            Session::put($cacheKey, compact('categories'));

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
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = Session::get('excel_file_path');

            if (! $filePath || ! file_exists($filePath)) {
                return view('sheet-cek', [
                    'data' => [],
                    'error' => 'File tidak ditemukan',
                ]);
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false); // Need formulas for calculated values
            $spreadsheet = $reader->load($filePath);

            if (! $spreadsheet->sheetNameExists('cek')) {
                return view('sheet-cek', [
                    'data' => [],
                    'error' => 'Sheet "cek" tidak ditemukan',
                ]);
            }

            $worksheet = $spreadsheet->getSheetByName('cek');
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            // Helper to get cell value - handle formulas properly
            $getCellValue = function ($cell) {
                if ($cell === null) {
                    return null;
                }

                try {
                    // Try to get calculated value if cell has formula
                    if ($cell->getDataType() === DataType::TYPE_FORMULA) {
                        $calculatedValue = $cell->getCalculatedValue();
                        if ($calculatedValue !== null && $calculatedValue !== '') {
                            return $calculatedValue;
                        }
                    }
                } catch (\Exception $e) {
                    // If formula calculation fails, continue to raw value
                }

                // Get regular value
                $value = $cell->getValue();

                // Skip formula strings that appear as text (formula failed to evaluate)
                if (is_string($value) && strpos($value, '=') === 0) {
                    return null;
                }

                return $value;
            };

            // Excluded columns
            $excludedColumns = ['V', 'W'];

            // Find header row - scan first 30 rows
            $headerRowNum = 1;
            for ($row = 1; $row <= min($highestRow, 30); $row++) {
                $headerCount = 0;
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    if (in_array($col, $excludedColumns)) {
                        continue;
                    }
                    $val = $getCellValue($worksheet->getCell($col.$row));
                    if ($val && (strtoupper(trim((string) $val)) === 'NO' || stripos(trim((string) $val), 'nomor') !== false)) {
                        $headerRowNum = $row;
                        break 2;
                    }
                }
            }

            // Get headers (only those that have a non-empty value, plus explicit G/H)
            $headers = [];
            $foundPajak = false;
            $totalAfterPajakCount = 0;
            $colToKey = [];

            for ($colIndex = 1; $colIndex <= Coordinate::columnIndexFromString($highestColumn); $colIndex++) {
                $col = Coordinate::stringFromColumnIndex($colIndex);
                if (in_array($col, $excludedColumns)) {
                    continue;
                }

                $cell = $worksheet->getCell($col.$headerRowNum);
                $headerValue = $getCellValue($cell);

                $name = $headerValue ? trim((string) $headerValue) : '';
                $key = $name;

                // Force mapping for G and H if they are empty
                if ($col === 'G' && $name === '') {
                    $name = ' ';
                    $key = 'DPP';
                } elseif ($col === 'H' && $name === '') {
                    $name = '  ';
                    $key = 'KETERANGAN';
                }

                if ($name !== '') {
                    if (strtoupper(trim($name)) === 'PAJAK') {
                        $foundPajak = true;
                    } elseif ($foundPajak && strtoupper(trim($name)) === 'TOTAL') {
                        $totalAfterPajakCount++;
                        if ($totalAfterPajakCount === 1) {
                            $key = 'TOTAL_1';
                        } elseif ($totalAfterPajakCount === 2) {
                            $key = 'TOTAL_2';
                        } elseif ($totalAfterPajakCount === 3) {
                            $key = 'TOTAL_3';
                        }
                    }

                    $headers[$col] = $name;
                    $colToKey[$col] = $key;
                }
            }

            // Merged cells info for rowspan
            $mergedCells = $worksheet->getMergeCells();
            $rowspanMap = [];
            $skipMap = [];

            foreach ($mergedCells as $range) {
                if (strpos($range, ':') !== false) {
                    [$start, $end] = explode(':', $range);
                    $startCol = preg_replace('/[0-9]/', '', $start);
                    $startRow = (int) preg_replace('/[A-Z]/', '', $start);
                    $endCol = preg_replace('/[0-9]/', '', $end);
                    $endRow = (int) preg_replace('/[A-Z]/', '', $end);

                    if ($startCol === $endCol) {
                        $rowspan = $endRow - $startRow + 1;
                        $rowspanMap[$startCol][$startRow] = $rowspan;
                        for ($r = $startRow + 1; $r <= $endRow; $r++) {
                            $skipMap[$startCol][$r] = true;
                        }
                    }
                }
            }

            $dataStartRow = $headerRowNum + 1;
            $maxRowsToProcess = min(50000, $highestRow);

            $rows = [];
            for ($row = $dataStartRow; $row <= $maxRowsToProcess; $row++) {
                $rowData = [];
                $hasData = false;
                $isOpeningKasasi = false;
                $onlyFilledUpToTim = true;
                $foundTim = false;
                $rowspans = [];

                foreach ($colToKey as $col => $key) {
                    $cell = $worksheet->getCell($col.$row);

                    if (isset($skipMap[$col][$row])) {
                        $rowData[$key] = 'SKIP_OR_NULL';
                        $hasData = true;

                        continue;
                    }

                    $value = $getCellValue($cell);
                    $rowData[$key] = $value;

                    if (isset($rowspanMap[$col][$row])) {
                        $rowspans[$key] = $rowspanMap[$col][$row];
                    }

                    if ($value !== null && $value !== '') {
                        $hasData = true;
                        if ($key === 'BIAYA' && (int) $value === 250000) {
                            $isOpeningKasasi = true;
                        }
                        if ($key === 'TIM') {
                            $foundTim = true;
                        }
                        if ($foundTim && ! in_array($key, ['TIM', 'TOTAL_1', 'TOTAL_2', 'TOTAL_3'])) {
                            $onlyFilledUpToTim = false;
                        }
                    }
                }

                if ($hasData) {
                    if ($isOpeningKasasi || ($foundTim && $onlyFilledUpToTim)) {
                        $rowData['TOTAL_1'] = null;
                        $rowData['TOTAL_2'] = null;
                        if (! isset($rowspans['TOTAL_3'])) {
                            $rowData['TOTAL_3'] = null;
                        }
                    }

                    if (isset($rowData['PAJAK']) && ($rowData['PAJAK'] === null || $rowData['PAJAK'] === '')) {
                        $rowData['TOTAL_1'] = null;
                        unset($rowspans['TOTAL_1']);
                    }

                    $rowData['_rowspans'] = $rowspans;
                    $rowData['_original_row'] = $row;
                    $rows[] = $rowData;
                }
            }

            // SMART CALCULATION
            for ($idx = 0; $idx < count($rows); $idx++) {
                $r = &$rows[$idx];
                if (isset($r['_rowspans']['TOTAL_2']) && ($r['TOTAL_2'] === null || $r['TOTAL_2'] === '')) {
                    $sum = 0;
                    $span = $r['_rowspans']['TOTAL_2'];
                    for ($i = 0; $i < $span && ($idx + $i) < count($rows); $i++) {
                        $comp = $rows[$idx + $i]['TOTAL_1'];
                        if (is_numeric($comp)) {
                            $sum += (float) $comp;
                        }
                    }
                    if ($sum > 0) {
                        $r['TOTAL_2'] = $sum;
                    }
                }
            }
            unset($r);

            for ($idx = 0; $idx < count($rows); $idx++) {
                $r = &$rows[$idx];
                if (isset($r['_rowspans']['TOTAL_3']) && ($r['TOTAL_3'] === null || $r['TOTAL_3'] === '')) {
                    $sum = 0;
                    $span = $r['_rowspans']['TOTAL_3'];
                    for ($i = 0; $i < $span && ($idx + $i) < count($rows); $i++) {
                        $comp = $rows[$idx + $i]['TOTAL_2'];
                        if ($comp !== 'SKIP_OR_NULL' && is_numeric($comp)) {
                            $sum += (float) $comp;
                        }
                    }
                    if ($sum > 0) {
                        $r['TOTAL_3'] = $sum;
                    }
                }
            }
            unset($r);

            // Extract footer/signature
            $tableData = [];
            $footerData = [];
            foreach ($rows as $row) {
                $isFooter = false;
                $rowText = '';
                foreach ($row as $k => $v) {
                    if ($k !== '_rowspans' && $k !== '_original_row' && $v !== 'SKIP_OR_NULL') {
                        $rowText .= ' '.(string) $v;
                    }
                }

                if (stripos($rowText, 'BENDAHARA') !== false ||
                    stripos($rowText, 'MENGETAHUI') !== false ||
                    stripos($rowText, 'PETUGAS') !== false ||
                    stripos($rowText, 'KUASA PENGELOLA') !== false ||
                    stripos($rowText, 'Jakarta,') !== false ||
                    stripos($rowText, 'ASEP NURSOBAH') !== false ||
                    stripos($rowText, 'FARIDA') !== false ||
                    stripos($rowText, 'KRIS NUGROHO') !== false ||
                    preg_match('/[A-Z]{2,}\s?,\s?S\.H\./', $rowText)) {
                    $isFooter = true;
                }

                if ($isFooter) {
                    $footerData[] = $row;
                } else {
                    $tableData[] = $row;
                }
            }

            $spreadsheet->disconnectWorksheets();

            return view('sheet-cek', [
                'data' => $tableData,
                'footer' => $footerData,
                'headers' => $headers,
                'colToKey' => $colToKey,
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

    public function printRekapKeseluruhan()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-print', [
                    'fileName' => $fileName,
                    'sheetName' => null,
                    'report' => [],
                    'error' => 'File tidak ditemukan. Silakan upload file terlebih dahulu.',
                ]);
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
            $recapDate = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : null;

            $sheet = $spreadsheet->getSheetByName('Rekap Keseluruhan')
                ?? $spreadsheet->getSheetByName('REKAP GABUNGAN');

            if (! $sheet) {
                return view('rekap-print', [
                    'fileName' => $fileName,
                    'sheetName' => null,
                    'report' => [],
                    'error' => 'Sheet rekap tidak ditemukan dalam file.',
                ]);
            }

            $report = $this->buildRekapKeseluruhanReport($sheet);

            $reportLabel = 'PERKARA ELEKTRONIK';
            $summarySheet = $spreadsheet->getSheetByName('REKAP GABUNGAN');
            if ($summarySheet) {
                $summaryLabel = trim((string) $summarySheet->getCell('B10')->getFormattedValue());
                if ($summaryLabel !== '') {
                    $reportLabel = preg_replace('/\s*\([^)]*\)$/', '', $summaryLabel) ?: $summaryLabel;
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return view('rekap-print', [
                'fileName' => $fileName,
                'sheetName' => $sheet->getTitle(),
                'report' => $report,
                'recapDate' => $recapDate,
                'reportLabel' => $reportLabel,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error generating recap print', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return view('rekap-print', [
                'fileName' => Session::get('excel_file_name'),
                'sheetName' => null,
                'report' => [],
                'recapDate' => null,
                'reportLabel' => 'PERKARA ELEKTRONIK',
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    private function buildRekapKeseluruhanReport($worksheet): array
    {
        $lastColumn = 'N';
        $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);
        $lastRow = min((int) $worksheet->getHighestRow(), 49);

        $mergedRanges = [];
        foreach ($worksheet->getMergeCells() as $range) {
            [$startCell, $endCell] = explode(':', $range);
            [$startColumn, $startRow] = $this->splitCellReference($startCell);
            [$endColumn, $endRow] = $this->splitCellReference($endCell);

            if ($startRow > $lastRow) {
                continue;
            }

            $mergedRanges[$startCell] = [
                'rowspan' => min($endRow, $lastRow) - $startRow + 1,
                'colspan' => min($endColumn, $lastColumnIndex) - $startColumn + 1,
            ];
        }

        $coveredCells = [];
        foreach ($worksheet->getMergeCells() as $range) {
            [$startCell, $endCell] = explode(':', $range);
            [$startColumn, $startRow] = $this->splitCellReference($startCell);
            [$endColumn, $endRow] = $this->splitCellReference($endCell);

            if ($startRow > $lastRow) {
                continue;
            }

            for ($row = $startRow; $row <= min($endRow, $lastRow); $row++) {
                for ($column = $startColumn; $column <= min($endColumn, $lastColumnIndex); $column++) {
                    $cellReference = Coordinate::stringFromColumnIndex($column).$row;
                    if ($cellReference !== $startCell) {
                        $coveredCells[$cellReference] = true;
                    }
                }
            }
        }

        $rows = [];
        for ($row = 1; $row <= $lastRow; $row++) {
            $cells = [];
            $hasData = false;
            for ($column = 1; $column <= $lastColumnIndex; $column++) {
                $cellReference = Coordinate::stringFromColumnIndex($column).$row;

                if (isset($coveredCells[$cellReference])) {
                    continue;
                }

                $cell = $worksheet->getCell($cellReference);
                $value = trim((string) $cell->getFormattedValue());

                try {
                    $rawValue = $cell->getCalculatedValue();
                } catch (\Throwable $e) {
                    $rawValue = $cell->getValue();
                }
                $rawStr = trim((string) ($rawValue ?? ''));

                if ($rawStr !== '' && $rawStr !== '0' && $rawStr !== '-' && ! (is_numeric($rawStr) && (float) $rawStr == 0)) {
                    $hasData = true;
                }

                $cells[] = [
                    'reference' => $cellReference,
                    'value' => $value,
                    'rowspan' => $mergedRanges[$cellReference]['rowspan'] ?? 1,
                    'colspan' => $mergedRanges[$cellReference]['colspan'] ?? 1,
                ];
            }

            $rows[] = [
                'number' => $row,
                'cells' => $cells,
                'hasData' => $hasData,
            ];
        }

        return [
            'rows' => $rows,
            'lastColumn' => $lastColumn,
            'lastRow' => $lastRow,
        ];
    }

    private function splitCellReference(string $cellReference): array
    {
        preg_match('/^([A-Z]+)(\d+)$/', $cellReference, $matches);

        return [
            Coordinate::columnIndexFromString($matches[1]),
            (int) $matches[2],
        ];
    }

    private function parseDataPrintSheet($worksheet)
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumnLetter = $worksheet->getHighestColumn();
        $rowsPerCategory = 500;

        $indexToColumn = function ($index) {
            $letter = '';
            while ($index > 0) {
                $index--;
                $letter = chr(65 + ($index % 26)).$letter;
                $index = intdiv($index, 26);
            }

            return $letter;
        };

        $columnToIndex = function ($col) {
            $index = 0;
            for ($i = 0; $i < strlen($col); $i++) {
                $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
            }

            return $index;
        };

        $getCellValue = function ($cell) {
            // Gunakan getValue() langsung (bukan getCalculatedValue) karena
            // sheet Data Print berisi data mentah, bukan formula kompleks.
            // getCalculatedValue() sangat lambat karena menghitung semua formula.
            $val = $cell->getValue();
            if (is_string($val) && strpos($val, '=') === 0) {
                return null;
            }

            return $val;
        };

        $isValidDataRow = function ($rowData, $currentHeaders) {
            $firstCell = null;
            foreach ($currentHeaders as $col => $header) {
                if ($header === 'No' && isset($rowData[$header])) {
                    $firstCell = $rowData[$header];
                    break;
                }
            }
            if (! $firstCell) {
                return false;
            }
            $firstCellStr = trim((string) $firstCell);
            if (strtoupper($firstCellStr) === 'NO' || strtoupper($firstCellStr) === 'NUMBER') {
                return false;
            }
            if (stripos($firstCellStr, 'PERKARA') !== false || stripos($firstCellStr, 'TOTAL') !== false || stripos($firstCellStr, 'DATA') !== false || strpos($firstCellStr, '~') !== false) {
                return false;
            }
            if (! is_numeric($firstCellStr) && ! ctype_digit($firstCellStr)) {
                return false;
            }
            $meaningfulCount = 0;
            foreach ($rowData as $value) {
                $val = trim((string) $value);
                if ($val !== '' && $val !== '-' && $val !== '~') {
                    $meaningfulCount++;
                }
            }

            return $meaningfulCount > 1;
        };

        $highestColumnIndex = $columnToIndex($highestColumnLetter);

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

        $categories = [];
        foreach ($categoryDefinitions as $title => $id) {
            $categories[$id] = [
                'id' => $id,
                'title' => $title,
                'data' => [],
                'count' => 0,
                'columns' => [],
                'total' => null,
            ];
        }

        $currentSection = null;
        $currentHeaderRow = null;
        $currentHeaders = [];
        $maxRowsToProcess = min(50000, $highestRow);

        for ($row = 1; $row <= $maxRowsToProcess; $row++) {
            $firstCell = trim($worksheet->getCell('A'.$row)->getValue() ?? '');
            $isSectionHeader = false;
            foreach ($categoryDefinitions as $sectionTitle => $sectionId) {
                if (stripos($firstCell, $sectionTitle) !== false) {
                    $currentSection = $sectionId;
                    $isSectionHeader = true;
                    break;
                }
            }
            if ($isSectionHeader) {
                $currentHeaderRow = null;

                continue;
            }
            if ($currentSection && $firstCell === 'No' && $currentHeaderRow === null) {
                $currentHeaderRow = $row;
                $currentHeaders = [];
                for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
                    $col = $indexToColumn($colIndex);
                    $header = trim($worksheet->getCell($col.$row)->getValue() ?? '');
                    $currentHeaders[$col] = $header ?: $col;
                }
                if ($currentSection) {
                    $categories[$currentSection]['columns'] = $currentHeaders;
                }

                continue;
            }
            if ($currentSection && $currentHeaderRow !== null && $row > $currentHeaderRow) {
                $rowData = [];
                $hasData = false;
                for ($colIndex = 1; $colIndex <= $highestColumnIndex; $colIndex++) {
                    $col = $indexToColumn($colIndex);
                    $value = $getCellValue($worksheet->getCell($col.$row));
                    if ($value !== null && $value !== '') {
                        $hasData = true;
                    }
                    $key = $currentHeaders[$col] ?? $col;
                    $rowData[$key] = $value;
                }
                if ($hasData) {
                    $firstCellVal = $rowData['No'] ?? null;
                    if ($firstCellVal && stripos(trim((string) $firstCellVal), 'TOTAL') !== false) {
                        $secondCol = null;
                        $cIdx = 0;
                        foreach ($currentHeaders as $c => $h) {
                            $cIdx++;
                            if ($cIdx === 2) {
                                $secondCol = $h;
                                break;
                            }
                        }
                        if ($secondCol && isset($rowData[$secondCol])) {
                            $categories[$currentSection]['total'] = $rowData[$secondCol];
                        }

                        continue;
                    }
                }
                if ($hasData && $isValidDataRow($rowData, $currentHeaders)) {
                    if ($categories[$currentSection]['count'] < $rowsPerCategory) {
                        $categories[$currentSection]['data'][] = $rowData;
                    }
                    $categories[$currentSection]['count']++;
                }
            }
        }

        foreach ($categories as &$category) {
            if ($category['count'] > 0 && ($category['total'] === null || $category['total'] === '')) {
                $category['total'] = $category['count'];
            }
        }

        return array_values($categories);
    }

    public function getSheet($sheetName)
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $sheets = Session::get('excel_sheets', []);
            $filePath = Session::get('excel_file_path');

            if (! in_array($sheetName, $sheets)) {
                return response()->json(['success' => false, 'message' => 'Sheet tidak ditemukan'], 404);
            }

            if (! $filePath || ! file_exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'File tidak ditemukan'], 404);
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getSheetByName($sheetName);

            $data = [];
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            $getCellValue = function ($cell) {
                try {
                    $value = $cell->getCalculatedValue();
                    if (is_string($value) && strpos($value, '=') === 0) {
                        return null;
                    }

                    return $value;
                } catch (\Exception $e) {
                    $val = $cell->getValue();
                    if (is_string($val) && strpos($val, '=') === 0) {
                        return null;
                    }

                    return $val;
                }
            };

            $headerRow = 1;
            for ($row = 1; $row <= min(20, $highestRow); $row++) {
                $foundNo = false;
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $val = $getCellValue($worksheet->getCell($col.$row));
                    if ($val && (strtoupper(trim((string) $val)) === 'NO' || stripos(trim((string) $val), 'nomor') !== false)) {
                        $headerRow = $row;
                        $foundNo = true;
                        break;
                    }
                }
                if ($foundNo) {
                    break;
                }
            }

            $headers = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cell = $worksheet->getCell($col.$headerRow);
                $headers[$col] = trim($getCellValue($cell) ?: $col);
            }

            $maxRows = min($highestRow, 50000);
            for ($row = $headerRow + 1; $row <= $maxRows; $row++) {
                $rowData = [];
                $hasData = false;
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $value = $getCellValue($worksheet->getCell($col.$row));
                    $rowData[$headers[$col] ?: $col] = $value;
                    if ($value !== null && $value !== '') {
                        $hasData = true;
                    }
                }
                if ($hasData) {
                    $data[] = $rowData;
                }
            }

            $spreadsheet->disconnectWorksheets();

            return response()->json(['success' => true, 'sheet' => $sheetName, 'data' => $data, 'count' => count($data)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 400);
        }
    }

    private function getPhpUploadLimitKb(): int
    {
        $uploadKb = $this->iniSizeToKb((string) ini_get('upload_max_filesize'));
        $postKb = $this->iniSizeToKb((string) ini_get('post_max_size'));
        if ($uploadKb <= 0 || $postKb <= 0) {
            return self::APP_MAX_UPLOAD_KB;
        }

        return min($uploadKb, $postKb);
    }

    private function iniSizeToKb(string $value): int
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return 0;
        }
        $unit = strtolower(substr($trimmed, -1));
        $number = (float) $trimmed;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024),
            'm' => (int) ($number * 1024),
            'k' => (int) $number,
            default => (int) ($number / 1024),
        };
    }

    private function getUploadErrorMessage(UploadedFile $file): string
    {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas upload server.',
            UPLOAD_ERR_PARTIAL => 'Upload terputus.',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file.',
            default => 'Upload gagal.',
        };
    }

    /**
     * Generate cache key unik berdasarkan file path dan nama halaman.
     */
    private function getCacheKey(string $filePath, string $page): string
    {
        return 'excel_cache_'.md5($filePath).'_'.$page;
    }

    /**
     * Hapus semua cache session Excel (dipanggil saat file baru diload).
     */
    private function invalidateSessionCache(): void
    {
        $pages = ['data_print', 'sheet_cek', 'rekap_keseluruhan', 'rekap_keseluruhan_print', 'rekap_keseluruhan_2', 'rekap_keseluruhan_3'];
        // Hapus berdasarkan file path lama
        $oldPath = Session::get('excel_file_path', '');
        if ($oldPath) {
            foreach ($pages as $page) {
                Session::forget($this->getCacheKey($oldPath, $page));
            }
        }
        // Hapus semua key excel_cache_* untuk berjaga-jaga
        foreach (array_keys(Session::all()) as $key) {
            if (str_starts_with($key, 'excel_cache_')) {
                Session::forget($key);
            }
        }
    }

    public function rekapKeseluruhan()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-keseluruhan', [
                    'fileName' => null,
                    'tableData' => [],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'File tidak ditemukan. Silakan upload file terlebih dahulu.',
                ]);
            }

            // Ambil dari cache session jika tersedia
            $cacheKey = $this->getCacheKey($filePath, 'rekap_keseluruhan');
            $cached = Session::get($cacheKey);

            if ($cached !== null) {
                \Log::info('rekapKeseluruhan() - loaded from session cache');

                return view('rekap-keseluruhan', [
                    'fileName' => $fileName,
                    'report' => $cached['report'],
                    'title1' => $cached['title1'],
                    'title2' => $cached['title2'],
                    'recapDate' => $cached['recapDate'],
                    'error' => null,
                ]);
            }

            \Log::info('rekapKeseluruhan() - loading from file (no cache)');
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $sheet = $spreadsheet->getSheetByName('Rekap Keseluruhan')
                ?? $spreadsheet->getSheetByName('REKAP GABUNGAN')
                ?? $spreadsheet->getSheetByName('rekap keseluruhan');

            if (! $sheet) {
                $spreadsheet->disconnectWorksheets();

                return view('rekap-keseluruhan', [
                    'fileName' => $fileName,
                    'tableData' => [],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'Sheet "Rekap Keseluruhan" tidak ditemukan dalam file.',
                ]);
            }

            $report = $this->buildRekapKeseluruhanReport($sheet);

            // Get title lines
            $rows = collect($report['rows'])->keyBy('number');
            $getCellVal = function (int $rowNum, string $ref, string $default = '') use ($rows) {
                $row = $rows->get($rowNum);
                $cell = $row ? collect($row['cells'] ?? [])->firstWhere('reference', $ref) : null;

                return $cell['value'] ?? $default;
            };

            $title1 = $getCellVal(2, 'A2', 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS');
            $title2 = $getCellVal(3, 'A3', 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK');

            $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
            $recapDate = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : 'Jakarta, 05 Maret 2026';

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            // Simpan ke session cache
            Session::put($cacheKey, compact('report', 'title1', 'title2', 'recapDate'));

            return view('rekap-keseluruhan', [
                'fileName' => $fileName,
                'report' => $report,
                'title1' => $title1,
                'title2' => $title2,
                'recapDate' => $recapDate,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in rekapKeseluruhan', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return view('rekap-keseluruhan', [
                'fileName' => Session::get('excel_file_name'),
                'tableData' => [],
                'title1' => '',
                'title2' => '',
                'recapDate' => '',
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function rekapKeseluruhanPrint()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-keseluruhan-print', [
                    'fileName' => null,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'File tidak ditemukan.',
                ]);
            }

            // Ambil dari cache session jika tersedia (reuse cache rekap_keseluruhan)
            $cacheKey = $this->getCacheKey($filePath, 'rekap_keseluruhan');
            $cached = Session::get($cacheKey);

            if ($cached !== null) {
                \Log::info('rekapKeseluruhanPrint() - loaded from session cache');

                return view('rekap-keseluruhan-print', [
                    'fileName' => $fileName,
                    'report' => $cached['report'],
                    'title1' => $cached['title1'],
                    'title2' => $cached['title2'],
                    'recapDate' => $cached['recapDate'],
                    'error' => null,
                ]);
            }

            \Log::info('rekapKeseluruhanPrint() - loading from file (no cache)');
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $sheet = $spreadsheet->getSheetByName('Rekap Keseluruhan')
                ?? $spreadsheet->getSheetByName('REKAP GABUNGAN')
                ?? $spreadsheet->getSheetByName('rekap keseluruhan');

            if (! $sheet) {
                $spreadsheet->disconnectWorksheets();

                return view('rekap-keseluruhan-print', [
                    'fileName' => $fileName,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'Sheet "Rekap Keseluruhan" tidak ditemukan.',
                ]);
            }

            $report = $this->buildRekapKeseluruhanReport($sheet);

            $rows = collect($report['rows'])->keyBy('number');
            $getCellVal = function (int $rowNum, string $ref, string $default = '') use ($rows) {
                $row = $rows->get($rowNum);
                $cell = $row ? collect($row['cells'] ?? [])->firstWhere('reference', $ref) : null;

                return $cell['value'] ?? $default;
            };

            $title1 = $getCellVal(2, 'A2', 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS');
            $title2 = $getCellVal(3, 'A3', 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK');

            $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
            $recapDate = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : 'Jakarta, 05 Maret 2026';

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            // Simpan ke session cache (berbagi dengan rekapKeseluruhan)
            Session::put($cacheKey, compact('report', 'title1', 'title2', 'recapDate'));

            return view('rekap-keseluruhan-print', [
                'fileName' => $fileName,
                'report' => $report,
                'title1' => $title1,
                'title2' => $title2,
                'recapDate' => $recapDate,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in rekapKeseluruhanPrint', ['error' => $e->getMessage()]);

            return view('rekap-keseluruhan-print', [
                'fileName' => Session::get('excel_file_name'),
                'report' => ['rows' => []],
                'title1' => '',
                'title2' => '',
                'recapDate' => '',
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function rekapKeseluruhan2()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-keseluruhan-2', [
                    'fileName' => null,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'File tidak ditemukan. Silakan upload file terlebih dahulu.',
                ]);
            }

            // Reuse cache rekap_keseluruhan untuk title/recapDate
            $cacheKey = $this->getCacheKey($filePath, 'rekap_keseluruhan');
            $cacheKey2 = $this->getCacheKey($filePath, 'rekap_keseluruhan_2');
            $cached2 = Session::get($cacheKey2);

            if ($cached2 !== null) {
                \Log::info('rekapKeseluruhan2() - loaded from session cache');
                $cached = Session::get($cacheKey);

                return view('rekap-keseluruhan-2', [
                    'fileName' => $fileName,
                    'report' => $cached2['report'],
                    'title1' => $cached['title1'] ?? '',
                    'title2' => $cached['title2'] ?? '',
                    'recapDate' => $cached['recapDate'] ?? '',
                    'error' => null,
                ]);
            }

            \Log::info('rekapKeseluruhan2() - loading from file (no cache)');
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $sheet = $spreadsheet->getSheetByName('Rekap Keseluruhan')
                ?? $spreadsheet->getSheetByName('REKAP GABUNGAN')
                ?? $spreadsheet->getSheetByName('rekap keseluruhan');

            if (! $sheet) {
                $spreadsheet->disconnectWorksheets();

                return view('rekap-keseluruhan-2', [
                    'fileName' => $fileName,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'Sheet "Rekap Keseluruhan" tidak ditemukan dalam file.',
                ]);
            }

            $report = $this->buildRekapKananReport($sheet);

            // Get title & recapDate dari cache halaman 1 jika ada, else load ulang
            $cached = Session::get($cacheKey);
            if ($cached !== null) {
                $title1 = $cached['title1'];
                $title2 = $cached['title2'];
                $recapDate = $cached['recapDate'];
            } else {
                $rows = collect($report['rows'])->keyBy('number');
                $getCellVal = function (int $rowNum, string $ref, string $default = '') use ($rows) {
                    $row = $rows->get($rowNum);
                    $cell = $row ? collect($row['cells'] ?? [])->firstWhere('reference', $ref) : null;

                    return $cell['value'] ?? $default;
                };
                $title1 = $getCellVal(2, 'A2', 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS');
                $title2 = $getCellVal(3, 'A3', 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK');
                $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
                $recapDate = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : 'Jakarta, 05 Maret 2026';
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            Session::put($cacheKey2, compact('report'));

            return view('rekap-keseluruhan-2', [
                'fileName' => $fileName,
                'report' => $report,
                'title1' => $title1,
                'title2' => $title2,
                'recapDate' => $recapDate,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in rekapKeseluruhan2', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return view('rekap-keseluruhan-2', [
                'fileName' => Session::get('excel_file_name'),
                'report' => ['rows' => []],
                'title1' => '',
                'title2' => '',
                'recapDate' => '',
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function rekapKeseluruhan2Print()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-keseluruhan-2-print', [
                    'fileName' => null,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'File tidak ditemukan.',
                ]);
            }

            $cacheKey = $this->getCacheKey($filePath, 'rekap_keseluruhan');
            $cacheKey2 = $this->getCacheKey($filePath, 'rekap_keseluruhan_2');
            $cached2 = Session::get($cacheKey2);

            if ($cached2 !== null) {
                \Log::info('rekapKeseluruhan2Print() - loaded from session cache');
                $cached = Session::get($cacheKey);

                return view('rekap-keseluruhan-2-print', [
                    'fileName' => $fileName,
                    'report' => $cached2['report'],
                    'title1' => $cached['title1'] ?? '',
                    'title2' => $cached['title2'] ?? '',
                    'recapDate' => $cached['recapDate'] ?? '',
                    'error' => null,
                ]);
            }

            \Log::info('rekapKeseluruhan2Print() - loading from file (no cache)');
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $sheet = $spreadsheet->getSheetByName('Rekap Keseluruhan')
                ?? $spreadsheet->getSheetByName('REKAP GABUNGAN')
                ?? $spreadsheet->getSheetByName('rekap keseluruhan');

            if (! $sheet) {
                $spreadsheet->disconnectWorksheets();

                return view('rekap-keseluruhan-2-print', [
                    'fileName' => $fileName,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'Sheet tidak ditemukan.',
                ]);
            }

            $report = $this->buildRekapKananReport($sheet);

            $cached = Session::get($cacheKey);
            if ($cached !== null) {
                $title1 = $cached['title1'];
                $title2 = $cached['title2'];
                $recapDate = $cached['recapDate'];
            } else {
                $rows = collect($report['rows'])->keyBy('number');
                $getCellVal = function (int $rowNum, string $ref, string $default = '') use ($rows) {
                    $row = $rows->get($rowNum);
                    $cell = $row ? collect($row['cells'] ?? [])->firstWhere('reference', $ref) : null;

                    return $cell['value'] ?? $default;
                };
                $title1 = $getCellVal(2, 'A2', 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS');
                $title2 = $getCellVal(3, 'A3', 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK');
                $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
                $recapDate = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : 'Jakarta, 05 Maret 2026';
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            Session::put($cacheKey2, compact('report'));

            return view('rekap-keseluruhan-2-print', [
                'fileName' => $fileName,
                'report' => $report,
                'title1' => $title1,
                'title2' => $title2,
                'recapDate' => $recapDate,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in rekapKeseluruhan2Print', ['error' => $e->getMessage()]);

            return view('rekap-keseluruhan-2-print', [
                'fileName' => Session::get('excel_file_name'),
                'report' => ['rows' => []],
                'title1' => '',
                'title2' => '',
                'recapDate' => '',
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Build tabel kanan atas dari sheet Rekap Keseluruhan.
     * Membaca kolom Q(17)–AY(51), baris 4–38.
     * Struktur: NO | PERUNTUKAN | % | 10×(BIAYA|JML|SUB TOTAL) | TOTAL
     */
    private function buildRekapKananReport($worksheet): array
    {
        // Kolom Q=17 s/d AY=51, baris 4–38
        $startColIdx = Coordinate::columnIndexFromString('Q'); // 17
        $endColIdx = Coordinate::columnIndexFromString('AY'); // 51
        $startRow = 4;
        $endRow = 38;

        // Kumpulkan merged ranges dalam area ini
        $mergedRanges = [];
        $coveredCells = [];

        foreach ($worksheet->getMergeCells() as $range) {
            [$startCell, $endCell] = explode(':', $range);
            [$sc, $sr] = $this->splitCellReference($startCell);
            [$ec, $er] = $this->splitCellReference($endCell);

            // Filter hanya yang overlap dengan area kanan (Q-AY, baris 4-38)
            if ($ec < $startColIdx || $sc > $endColIdx) {
                continue;
            }
            if ($er < $startRow || $sr > $endRow) {
                continue;
            }

            // Clamp ke area
            $clampedEc = min($ec, $endColIdx);
            $clampedEr = min($er, $endRow);
            $clampedSc = max($sc, $startColIdx);
            $clampedSr = max($sr, $startRow);

            $mergedRanges[$startCell] = [
                'rowspan' => $clampedEr - $sr + 1,
                'colspan' => $clampedEc - $sc + 1,
            ];

            // Tandai covered cells (kecuali start cell sendiri)
            for ($r = $sr; $r <= $clampedEr; $r++) {
                for ($c = $sc; $c <= $clampedEc; $c++) {
                    $ref = Coordinate::stringFromColumnIndex($c).$r;
                    if ($ref !== $startCell) {
                        $coveredCells[$ref] = true;
                    }
                }
            }
        }

        $rows = [];
        for ($row = $startRow; $row <= $endRow; $row++) {
            $cells = [];
            $hasData = false; // flag: ada setidaknya satu cell dengan data bermakna

            for ($colIdx = $startColIdx; $colIdx <= $endColIdx; $colIdx++) {
                $cellRef = Coordinate::stringFromColumnIndex($colIdx).$row;

                if (isset($coveredCells[$cellRef])) {
                    continue;
                }

                $cell = $worksheet->getCell($cellRef);
                $value = trim((string) $cell->getFormattedValue());

                // Gunakan raw value untuk mendeteksi apakah ada data bermakna
                try {
                    $rawValue = $cell->getCalculatedValue();
                } catch (\Throwable $e) {
                    $rawValue = $cell->getValue();
                }
                $rawStr = trim((string) ($rawValue ?? ''));

                // Data bermakna = bukan kosong, bukan nol, bukan "-"
                if ($rawStr !== '' && $rawStr !== '0' && $rawStr !== '-' && ! (is_numeric($rawStr) && (float) $rawStr == 0)) {
                    $hasData = true;
                }

                $cells[] = [
                    'reference' => $cellRef,
                    'value' => $value,
                    'rowspan' => $mergedRanges[$cellRef]['rowspan'] ?? 1,
                    'colspan' => $mergedRanges[$cellRef]['colspan'] ?? 1,
                ];
            }

            $rows[] = [
                'number' => $row,
                'cells' => $cells,
                'hasData' => $hasData, // baris bermakna jika minimal ada 1 cell non-kosong/non-nol
            ];
        }

        return [
            'rows' => $rows,
            'startCol' => 'Q',
            'endCol' => 'AY',
            'startRow' => $startRow,
            'endRow' => $endRow,
        ];
    }

    /**
     * DEBUG: Tampilkan struktur raw sheet honorarium untuk diagnosa
     * Akses: /honorarium/debug
     */
    public function honorariumDebug()
    {
        ini_set('memory_limit', '1024M');
        $filePath = Session::get('excel_file_path');
        if (! $filePath || ! file_exists($filePath)) {
            return response()->json(['error' => 'Tidak ada file. Upload dulu.'], 404);
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load($filePath);

        $result = [];
        foreach ($spreadsheet->getAllSheets() as $ws) {
            $lower = strtolower($ws->getTitle());
            if (! str_contains($lower, 'honor')) {
                continue;
            }

            $highestRow = $ws->getHighestRow();
            $highestColIdx = Coordinate::columnIndexFromString($ws->getHighestColumn());

            // Cari header row
            $headerRow = null;
            for ($r = 1; $r <= min($highestRow, 20); $r++) {
                $hasNo = $hasNama = false;
                for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
                    $v = strtoupper(trim((string) $ws->getCell(Coordinate::stringFromColumnIndex($c).$r)->getFormattedValue()));
                    if (in_array($v, ['NO', 'NO.', 'NOMOR'])) {
                        $hasNo = true;
                    }
                    if (str_contains($v, 'NAMA')) {
                        $hasNama = true;
                    }
                }
                if ($hasNo && $hasNama) {
                    $headerRow = $r;
                    break;
                }
            }

            // Baca headers
            $headers = [];
            for ($c = 1; $c <= $highestColIdx; $c++) {
                $v = trim((string) $ws->getCell(Coordinate::stringFromColumnIndex($c).$headerRow)->getFormattedValue());
                $headers[$c] = $v ?: Coordinate::stringFromColumnIndex($c);
            }

            // Baca 30 baris pertama data
            $sampleRows = [];
            for ($r = $headerRow + 1; $r <= min($highestRow, $headerRow + 50); $r++) {
                $rowData = [];
                $hasContent = false;
                for ($c = 1; $c <= $highestColIdx; $c++) {
                    $cell = $ws->getCell(Coordinate::stringFromColumnIndex($c).$r);
                    try {
                        $cell->getCalculatedValue();
                    } catch (\Throwable $e) {
                    }
                    $v = trim((string) $cell->getFormattedValue());
                    $rowData['col_'.Coordinate::stringFromColumnIndex($c).'_'.$headers[$c]] = $v;
                    if ($v !== '') {
                        $hasContent = true;
                    }
                }
                if ($hasContent) {
                    $sampleRows[] = $rowData;
                }
                if (count($sampleRows) >= 30) {
                    break;
                }
            }

            $result[$ws->getTitle()] = [
                'headerRow' => $headerRow,
                'headers' => $headers,
                'totalCols' => $highestColIdx,
                'sampleRows' => $sampleRows,
            ];
        }

        $spreadsheet->disconnectWorksheets();

        return response()->json($result, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function honorarium()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);

            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('honorarium', [
                    'fileName' => null,
                    'sheets' => [],
                    'activeSheet' => null,
                    'error' => 'File tidak ditemukan. Silakan upload file terlebih dahulu.',
                ]);
            }

            // Cek cache — invalidate otomatis jika data lama (tidak ada _kode_kuitansi)
            $cacheKey = $this->getCacheKey($filePath, 'honorarium');
            $cached = Session::get($cacheKey);
            if ($cached !== null) {
                // Validasi: pastikan data cache punya field _kode_kuitansi (fitur v2)
                $firstSheet = $cached['sheets'][0] ?? null;
                $firstRow = $firstSheet['rows'][0] ?? null;
                $hasKode = $firstRow !== null && array_key_exists('_kode_kuitansi', $firstRow);

                if ($hasKode) {
                    \Log::info('honorarium() - loaded from session cache (valid)');

                    return view('honorarium', array_merge($cached, [
                        'fileName' => $fileName,
                        'error' => null,
                    ]));
                }

                // Cache lama: hapus dan parse ulang
                \Log::info('honorarium() - cache invalidated (missing _kode_kuitansi), re-parsing');
                Session::forget($cacheKey);
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $allSheetNames = $spreadsheet->getSheetNames();

            // Sheet yang DIKETAHUI bukan honorarium dan harus dieksklusi
            $excludedSheets = [
                'Data Print',
                'cek', 'Cek',
                'Rekap Keseluruhan', 'REKAP GABUNGAN', 'rekap keseluruhan',
                'Periode Laporan',
                'Sheet1', 'Sheet2', 'Sheet3', 'Sheet4',
                'list baru', 'List Baru',
                'Print_Amplop', 'print_amplop',
                'PEMBAGIAN PER-ANMUD', 'Pembagian Per-Anmud',
                'Rekap Khusus PDT',
            ];

            // Cari sheet yang mengandung kata honorarium/honor
            $honorSheets = array_values(array_filter($allSheetNames, function ($name) {
                $lower = strtolower($name);
                return str_contains($lower, 'honor') || str_contains($lower, 'honorarium');
            }));

            // Jika tidak ada, ambil sheet berdasarkan nama yang dikenal
            if (empty($honorSheets)) {
                // Known honorarium sheet name patterns
                $knownHonorNames = ['op - staf', 'op-staf', 'opstaf', 'tim', 'kepaniteraan',
                    'rekap-kep', 'rekap-panmud', 'rekap kep', 'rekap panmud',
                    'kma', 'panitera', 'all panmud', 'allpanmud', 'rekap kma',
                    'pemilah', 'rekap pemilah', 'data print copy'];

                $honorSheets = array_values(array_filter($allSheetNames, function ($n) use ($excludedSheets, $knownHonorNames) {
                    if (in_array($n, $excludedSheets)) {
                        return false;
                    }
                    $lower = strtolower(trim($n));
                    // Eksklusi pattern: rekap-xxx (kecuali yg sudah ada di knownHonorNames)
                    foreach ($knownHonorNames as $known) {
                        if (str_contains($lower, $known)) {
                            return true;
                        }
                    }
                    return false;
                }));

                // Jika masih kosong, fallback ke semua kecuali excluded
                if (empty($honorSheets)) {
                    $honorSheets = array_values(array_filter($allSheetNames, fn ($n) => ! in_array($n, $excludedSheets)));
                }
            }

            $sheets = [];
            foreach ($honorSheets as $sheetName) {
                $ws = $spreadsheet->getSheetByName($sheetName);
                if (! $ws) {
                    continue;
                }

                $parsedTables = $this->parseHonorariumSheet($ws, $sheetName);
                if (!empty($parsedTables)) {
                    foreach ($parsedTables as $tblIdx => $parsedTable) {
                        if (count($parsedTables) > 1) {
                            $parsedTable['sheetName'] = $sheetName . ' (Bagian ' . ($tblIdx + 1) . ')';
                        }
                        $sheets[] = $parsedTable;
                    }
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            // Jika tidak ada sheet honorarium yang valid, tampilkan pesan
            if (empty($sheets)) {
                return view('honorarium', [
                    'fileName' => $fileName,
                    'sheets' => [],
                    'activeSheet' => null,
                    'error' => 'Sheet honorarium tidak ditemukan dalam file. Pastikan file memiliki sheet dengan nama mengandung kata "honor".',
                ]);
            }

            Session::put($cacheKey, compact('sheets'));

            return view('honorarium', [
                'fileName' => $fileName,
                'sheets' => $sheets,
                'activeSheet' => 0,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in honorarium', ['error' => $e->getMessage(), 'line' => $e->getLine()]);

            return view('honorarium', [
                'fileName' => Session::get('excel_file_name'),
                'sheets' => [],
                'activeSheet' => null,
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function honorariumPrint()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);

            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('honorarium-print', [
                    'fileName' => null,
                    'sheets' => [],
                    'error' => 'File tidak ditemukan.',
                ]);
            }

            $cacheKey = $this->getCacheKey($filePath, 'honorarium');
            $cached = Session::get($cacheKey);
            if ($cached !== null) {
                return view('honorarium-print', array_merge($cached, [
                    'fileName' => $fileName,
                    'error' => null,
                ]));
            }

            // Reuse logic from honorarium()
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);
            $allSheetNames = $spreadsheet->getSheetNames();

            $excludedSheets = [
                'Data Print',
                'cek', 'Cek',
                'Rekap Keseluruhan', 'REKAP GABUNGAN', 'rekap keseluruhan',
                'Periode Laporan',
                'Sheet1', 'Sheet2', 'Sheet3', 'Sheet4',
                'list baru', 'List Baru',
                'Print_Amplop', 'print_amplop',
                'PEMBAGIAN PER-ANMUD', 'Pembagian Per-Anmud',
                'Rekap Khusus PDT',
            ];

            $honorSheets = array_values(array_filter($allSheetNames, function ($name) {
                $lower = strtolower($name);
                return str_contains($lower, 'honor') || str_contains($lower, 'honorarium');
            }));
            if (empty($honorSheets)) {
                $knownHonorNames = ['op - staf', 'op-staf', 'opstaf', 'tim', 'kepaniteraan',
                    'rekap-kep', 'rekap-panmud', 'rekap kep', 'rekap panmud',
                    'kma', 'panitera', 'all panmud', 'allpanmud', 'rekap kma',
                    'pemilah', 'rekap pemilah', 'data print copy'];
                $honorSheets = array_values(array_filter($allSheetNames, function ($n) use ($excludedSheets, $knownHonorNames) {
                    if (in_array($n, $excludedSheets)) return false;
                    $lower = strtolower(trim($n));
                    foreach ($knownHonorNames as $known) {
                        if (str_contains($lower, $known)) return true;
                    }
                    return false;
                }));
                if (empty($honorSheets)) {
                    $honorSheets = array_values(array_filter($allSheetNames, fn ($n) => ! in_array($n, $excludedSheets)));
                }
            }

            $sheets = [];
            foreach ($honorSheets as $sheetName) {
                $ws = $spreadsheet->getSheetByName($sheetName);
                if (! $ws) {
                    continue;
                }
                $parsedTables = $this->parseHonorariumSheet($ws, $sheetName);
                if (!empty($parsedTables)) {
                    foreach ($parsedTables as $tblIdx => $parsedTable) {
                        if (count($parsedTables) > 1) {
                            $parsedTable['sheetName'] = $sheetName . ' (Bagian ' . ($tblIdx + 1) . ')';
                        }
                        $sheets[] = $parsedTable;
                    }
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            Session::put($cacheKey, compact('sheets'));

            return view('honorarium-print', [
                'fileName' => $fileName,
                'sheets' => $sheets,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in honorariumPrint', ['error' => $e->getMessage()]);

            return view('honorarium-print', [
                'fileName' => Session::get('excel_file_name'),
                'sheets' => [],
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Parse sebuah sheet honorarium.
     * Mendukung multiple sub-tabel dalam satu sheet (digabung menjadi satu tabel
     * dengan baris section-separator yang menyertakan judul sub-tabel).
     * Mengembalikan array berisi SATU entry (single table dengan semua rows).
     */
    private function parseHonorariumSheet($worksheet, string $sheetName): ?array

    {
        $highestRow    = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColIdx = Coordinate::columnIndexFromString($highestColumn);

        // ── 1. Cari SEMUA baris header (mengandung NO dan NAMA) ──────────────
        $headerRows = [];
        for ($r = 1; $r <= $highestRow; $r++) {
            $rowHasNo   = false;
            $rowHasNama = false;
            for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
                $val = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
                if ($val === '') {
                    continue;
                }
                $upper = strtoupper($val);
                // "NO" biasanya ada di kolom 1 atau 2. Batasi c <= 3 agar tidak match Amar "NO" di akhir tabel
                if ($c <= 3 && ($upper === 'NO' || $upper === 'NO.' || $upper === 'NOMOR')) {
                    $rowHasNo = true;
                }
                // Nama harus diawali kata NAMA, bukan sekadar str_contains (menghindari false positive nama orang yg mengandung 'nama' spt PURNAMA)
                if ($c <= 12 && (str_starts_with($upper, 'NAMA') || $upper === 'NAMA LENGKAP')) {
                    $rowHasNama = true;
                }
            }
            if ($rowHasNo && $rowHasNama) {
                $headerRows[] = $r;
            }
        }

        if (empty($headerRows)) {
            return null; // Sheet tanpa tabel
        }

        // ── 2. Ambil headers dari tabel PERTAMA (digunakan sebagai standar) ──
        $firstHRow = $headerRows[0];
        $masterHeaders = [];
        for ($c = 1; $c <= $highestColIdx; $c++) {
            $val = trim((string) $worksheet->getCell([$c, $firstHRow])->getFormattedValue());
            if ($val === '' && $firstHRow > 1) {
                $valAbove = trim((string) $worksheet->getCell([$c, $firstHRow - 1])->getFormattedValue());
                if ($valAbove !== '') {
                    $val = $valAbove;
                }
            }
            $masterHeaders[$c] = $val !== '' ? $val : Coordinate::stringFromColumnIndex($c);
        }

        // ── 3. Ambil judul dari baris 1 s/d firstHRow-1 (judul utama sheet) ─
        $mainTitleLines = [];
        for ($r = 1; $r < $firstHRow; $r++) {
            $rowText = '';
            for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
                $val = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
                if ($val !== '') {
                    $rowText .= ' ' . $val;
                }
            }
            $text = trim($rowText);
            if ($text !== '') {
                $mainTitleLines[] = $text;
            }
        }

        // ── 4. Gabungkan semua sub-tabel menjadi satu ────────────────────────
        $colHasContent = array_fill(1, $highestColIdx, false);
        $allRows       = [];
        $footerKeywords = [
            'jakarta', 'mengetahui', 'panitera mahkamah', 'petugas pembuat',
            'bendahara', 'kuasa pengelola', 'biaya proses',
        ];

        $timKeywords = [
            'hakim agung', 'hakim', 'askor', 'asisten koordinator',
            'panitera muda', 'panmud', 'asisten', 'operator', 'tim korektor',
        ];

        $lastSubTitle = '';

        foreach ($headerRows as $idx => $hRow) {
            // Batas akhir chunk ini
            $endBoundary = isset($headerRows[$idx + 1]) ? $headerRows[$idx + 1] - 1 : $highestRow;

            // Lebih baik: ambil judul dari baris yang ada text sebelum hRow
            $subTitleLines = [];
            
            // Cari batas atas sub-judul: scan mundur dari hRow-1
            $scanStart = $hRow - 1;
            $titleBoundary = -1;
            
            // Batasi scan mundur max 5 baris, dan tidak boleh melewati header sebelumnya
            $maxScan = max(1, $hRow - 5);
            if ($idx > 0) {
                $maxScan = max($maxScan, $headerRows[$idx - 1] + 1);
            }
            
            for ($r = $hRow - 1; $r >= $maxScan; $r--) {
                $hasText = false;
                for ($c = 1; $c <= min($highestColIdx, 12); $c++) {
                    if (trim((string) $worksheet->getCell([$c, $r])->getFormattedValue()) !== '') {
                        $hasText = true;
                        break;
                    }
                }
                
                if (!$hasText) {
                    break; // ketemu baris kosong
                }
                
                // Jika kolom pertama adalah angka, ini kemungkinan data row dari halaman sebelumnya, bukan title
                $val1 = trim((string) $worksheet->getCell([1, $r])->getFormattedValue());
                if (is_numeric($val1) && $val1 != '') {
                    break;
                }
                
                $titleBoundary = $r;
            }

            if ($titleBoundary !== -1) {
                for ($r = $titleBoundary; $r < $hRow; $r++) {
                    $rowText = '';
                    for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
                        $val = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
                        if ($val !== '') {
                            $rowText .= ' ' . $val;
                        }
                    }
                    $text = trim($rowText);
                    if ($text !== '') {
                        $subTitleLines[] = $text;
                    }
                }
            }

            $subTitle = implode(' | ', $subTitleLines);
            // Jika tidak ada title baru (misal karena header berulang untuk page break), gunakan title sebelumnya
            if ($subTitle === '' && $idx > 0) {
                $subTitle = $lastSubTitle;
            }
            $lastSubTitle = $subTitle;

            // Baca data baris
            $inFooter = false;
            for ($r = $hRow + 1; $r <= $endBoundary; $r++) {
                // Deteksi footer
                if (! $inFooter) {
                    $rowTextLow = '';
                    for ($c = 1; $c <= min($highestColIdx, 12); $c++) {
                        $rowTextLow .= ' ' . strtolower(trim((string) $worksheet->getCell([$c, $r])->getFormattedValue()));
                    }
                    foreach ($footerKeywords as $kw) {
                        if (str_contains($rowTextLow, $kw)) {
                            $inFooter = true;
                            break;
                        }
                    }
                }

                if ($inFooter) {
                    continue; // Skip footer rows
                }

                $rowData    = [];
                $rowHasData = false;
                for ($c = 1; $c <= $highestColIdx; $c++) {
                    $cell = $worksheet->getCell([$c, $r]);
                    try {
                        $cell->getCalculatedValue();
                    } catch (\Throwable $e) {
                        // abaikan error kalkulasi formula
                    }
                    $val = trim((string) $cell->getFormattedValue());
                    $hName = $masterHeaders[$c] ?? Coordinate::stringFromColumnIndex($c);
                    $rowData[$hName] = $val;
                    if ($val !== '') {
                        $rowHasData = true;
                        $colHasContent[$c] = true;
                    }
                }
                if ($rowHasData) {
                    $rowData['_section_title'] = $subTitle;
                    $allRows[] = $rowData;
                }
            }
        }

        if (empty($allRows)) {
            return null;
        }

        // ── 5. Hapus kolom sepenuhnya kosong ────────────────────────────────
        $activeHeaders = [];
        foreach ($masterHeaders as $c => $hName) {
            if ($colHasContent[$c]) {
                $activeHeaders[$c] = $hName;
            }
        }

        // ── 6. Filter rows agar hanya kolom aktif ────────────────────────────
        $filteredRows = array_map(function ($row) use ($activeHeaders) {
            $out = [];
            foreach ($activeHeaders as $hName) {
                $out[$hName] = $row[$hName] ?? '';
            }
            $out['_section_title'] = $row['_section_title'] ?? '';
            return $out;
        }, $allRows);

        // ── 7. Deteksi kolom jabatan & auto-koding kode_kuitansi ─────────────
        $jabatanColName = null;
        foreach ($activeHeaders as $hName) {
            $up = strtoupper(trim($hName));
            if (in_array($up, ['JABATAN', 'URAIAN', 'NAMA JABATAN', 'KETERANGAN', 'JABATAN/URAIAN'])) {
                $jabatanColName = $hName;
                break;
            }
            if (str_contains(strtolower($up), 'jabat') || str_contains(strtolower($up), 'urai')) {
                $jabatanColName = $hName;
                break;
            }
        }

        if ($jabatanColName === null && ! empty($filteredRows)) {
            $colMatchScore = [];
            foreach ($activeHeaders as $hName) {
                $up = strtoupper(trim($hName));
                if (in_array($up, ['BIAYA', 'JUMLAH', 'NETTO', 'PPH', 'PAJAK', 'TOTAL', 'NO', 'NO.'])
                    || str_contains($up, 'BIAYA') || str_contains($up, 'PPH') || str_contains($up, 'NETTO')) {
                    continue;
                }
                $score = 0;
                foreach ($filteredRows as $dataRow) {
                    $v = strtolower(trim((string) ($dataRow[$hName] ?? '')));
                    foreach ($timKeywords as $kw) {
                        if (str_contains($v, $kw)) {
                            $score++;
                            break;
                        }
                    }
                    $kepanitKeywords = ['panitera pengganti', 'juru sita', 'staf', 'pelaksana', 'kasubag', 'kabag'];
                    foreach ($kepanitKeywords as $kw) {
                        if (str_contains($v, $kw)) {
                            $score++;
                            break;
                        }
                    }
                }
                $colMatchScore[$hName] = $score;
            }
            if (! empty($colMatchScore)) {
                arsort($colMatchScore);
                $bestCol = array_key_first($colMatchScore);
                if ($colMatchScore[$bestCol] > 0) {
                    $jabatanColName = $bestCol;
                }
            }
        }

        foreach ($filteredRows as &$row) {
            $isTim  = false;
            $subKey = '';
            $jabVal = '';

            if ($jabatanColName !== null) {
                $jabVal = strtolower(trim((string) ($row[$jabatanColName] ?? '')));
            } else {
                $parts = [];
                foreach ($row as $k => $v) {
                    if (! str_starts_with((string) $k, '_')) {
                        $parts[] = strtolower(trim((string) $v));
                    }
                }
                $jabVal = implode(' ', $parts);
            }

            if ($jabVal !== '') {
                if (str_contains($jabVal, 'hakim agung') || str_contains($jabVal, 'hakim') || str_contains($jabVal, 'tim korektor')) {
                    $isTim  = true;
                    $subKey = 'hakim';
                } elseif (str_contains($jabVal, 'panmud') || str_contains($jabVal, 'panitera muda')
                    || str_contains($jabVal, 'askor') || str_contains($jabVal, 'asisten koordinator')) {
                    $isTim  = true;
                    $subKey = 'panmud';
                } elseif (str_contains($jabVal, 'operator')) {
                    $isTim  = true;
                    $subKey = 'operator';
                } elseif (str_contains($jabVal, 'asisten')) {
                    $isTim  = true;
                    $subKey = 'asisten';
                }
            }

            $row['_kode_kuitansi'] = $isTim ? 1 : 2;
            $row['_jabatan_sub']   = $subKey;
        }
        unset($row);

        return [[
            'sheetName'     => $sheetName,
            'title'         => implode("\n", array_slice($mainTitleLines, 0, 5)),
            'headers'       => $activeHeaders,
            'rows'          => $filteredRows,
            'footerBlocks'  => [],
            'jabatanColName' => $jabatanColName,
        ]];
    }
    /**
     * Kelompokkan baris footer menjadi blok tanda-tangan berdasarkan posisi kolom.
     * Output: array of [ 'position' => 'left'|'center'|'right', 'lines' => [...] ]
     */
    private function parseFooterBlocks(array $footerRows, array $headers, int $totalCols): array
    {
        if (empty($footerRows)) {
            return [];
        }

        // Pembagi posisi: 1/3 pertama = kiri, 1/3 tengah = center, 1/3 akhir = kanan
        $leftEnd = (int) ceil($totalCols / 3);
        $centerEnd = (int) ceil(2 * $totalCols / 3);

        $buckets = ['left' => [], 'center' => [], 'right' => []];

        foreach ($footerRows as $row) {
            foreach ($headers as $c => $hName) {
                $val = trim((string) ($row[$hName] ?? ''));
                if ($val === '') {
                    continue;
                }
                if ($c <= $leftEnd) {
                    $buckets['left'][] = $val;
                } elseif ($c <= $centerEnd) {
                    $buckets['center'][] = $val;
                } else {
                    $buckets['right'][] = $val;
                }
            }
        }

        $result = [];
        foreach (['left', 'center', 'right'] as $pos) {
            if (! empty($buckets[$pos])) {
                $result[] = [
                    'position' => $pos,
                    'lines' => array_values(array_unique($buckets[$pos])),
                ];
            }
        }

        return $result;
    }

    public function rekapKeseluruhan3()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-keseluruhan-3', [
                    'fileName' => null,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'File tidak ditemukan. Silakan upload file terlebih dahulu.',
                ]);
            }

            $cacheKey = $this->getCacheKey($filePath, 'rekap_keseluruhan');
            $cacheKey3 = $this->getCacheKey($filePath, 'rekap_keseluruhan_3');
            $cached3 = Session::get($cacheKey3);

            if ($cached3 !== null) {
                \Log::info('rekapKeseluruhan3() - loaded from session cache');
                $cached = Session::get($cacheKey);

                return view('rekap-keseluruhan-3', [
                    'fileName' => $fileName,
                    'report' => $cached3['report'],
                    'title1' => $cached['title1'] ?? '',
                    'title2' => $cached['title2'] ?? '',
                    'recapDate' => $cached['recapDate'] ?? '',
                    'error' => null,
                ]);
            }

            \Log::info('rekapKeseluruhan3() - loading from file (no cache)');
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $sheet = $spreadsheet->getSheetByName('Rekap Keseluruhan')
                ?? $spreadsheet->getSheetByName('REKAP GABUNGAN')
                ?? $spreadsheet->getSheetByName('rekap keseluruhan');

            if (! $sheet) {
                $spreadsheet->disconnectWorksheets();

                return view('rekap-keseluruhan-3', [
                    'fileName' => $fileName,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'Sheet "Rekap Keseluruhan" tidak ditemukan dalam file.',
                ]);
            }

            $report = $this->buildRekapHonorariumPerkaraReport($sheet);

            $cached = Session::get($cacheKey);
            if ($cached !== null) {
                $title1 = $cached['title1'];
                $title2 = $cached['title2'];
                $recapDate = $cached['recapDate'];
            } else {
                $title1 = 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS';
                $title2 = 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK';
                $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
                $recapDate = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : 'Jakarta, 05 Maret 2026';
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            Session::put($cacheKey3, compact('report'));

            return view('rekap-keseluruhan-3', [
                'fileName' => $fileName,
                'report' => $report,
                'title1' => $title1,
                'title2' => $title2,
                'recapDate' => $recapDate,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in rekapKeseluruhan3', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return view('rekap-keseluruhan-3', [
                'fileName' => Session::get('excel_file_name'),
                'report' => ['rows' => []],
                'title1' => '',
                'title2' => '',
                'recapDate' => '',
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function rekapKeseluruhan3Print()
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');
            set_time_limit(300);
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-keseluruhan-3-print', [
                    'fileName' => null,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'File tidak ditemukan.',
                ]);
            }

            $cacheKey = $this->getCacheKey($filePath, 'rekap_keseluruhan');
            $cacheKey3 = $this->getCacheKey($filePath, 'rekap_keseluruhan_3');
            $cached3 = Session::get($cacheKey3);

            if ($cached3 !== null) {
                \Log::info('rekapKeseluruhan3Print() - loaded from session cache');
                $cached = Session::get($cacheKey);

                return view('rekap-keseluruhan-3-print', [
                    'fileName' => $fileName,
                    'report' => $cached3['report'],
                    'title1' => $cached['title1'] ?? '',
                    'title2' => $cached['title2'] ?? '',
                    'recapDate' => $cached['recapDate'] ?? '',
                    'error' => null,
                ]);
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $sheet = $spreadsheet->getSheetByName('Rekap Keseluruhan')
                ?? $spreadsheet->getSheetByName('REKAP GABUNGAN')
                ?? $spreadsheet->getSheetByName('rekap keseluruhan');

            if (! $sheet) {
                $spreadsheet->disconnectWorksheets();

                return view('rekap-keseluruhan-3-print', [
                    'fileName' => $fileName,
                    'report' => ['rows' => []],
                    'title1' => '',
                    'title2' => '',
                    'recapDate' => '',
                    'error' => 'Sheet tidak ditemukan.',
                ]);
            }

            $report = $this->buildRekapHonorariumPerkaraReport($sheet);

            $cached = Session::get($cacheKey);
            if ($cached !== null) {
                $title1 = $cached['title1'];
                $title2 = $cached['title2'];
                $recapDate = $cached['recapDate'];
            } else {
                $title1 = 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS';
                $title2 = 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK';
                $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
                $recapDate = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : 'Jakarta, 05 Maret 2026';
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            Session::put($cacheKey3, compact('report'));

            return view('rekap-keseluruhan-3-print', [
                'fileName' => $fileName,
                'report' => $report,
                'title1' => $title1,
                'title2' => $title2,
                'recapDate' => $recapDate,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in rekapKeseluruhan3Print', ['error' => $e->getMessage()]);

            return view('rekap-keseluruhan-3-print', [
                'fileName' => Session::get('excel_file_name'),
                'report' => ['rows' => []],
                'title1' => '',
                'title2' => '',
                'recapDate' => '',
                'error' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Baca tabel honorarium perkara dari sheet Rekap Keseluruhan.
     *
     *
     * @param  Worksheet  $worksheet
     * @return array{rows: list<array{number: int, cells: list<array{reference: string, value: string, rowspan: int, colspan: int}>}>, headerRow: int|null, startColIdx: int, lastColIdx: int, signatureLines: list<string>}
     */
    private function buildRekapHonorariumPerkaraReport($worksheet): array
    {
        $highestRow = $worksheet->getHighestRow();
        $highestCol = $worksheet->getHighestColumn();
        $highestColIdx = Coordinate::columnIndexFromString($highestCol);

        // ══════════════════════════════════════════════════════════════════
        // Layout sheet Excel di bawah baris 38:
        //   Kolom A–P  (kiri)  : Blok tanda tangan (Jakarta, Bendahara, nama)
        //   Kolom Q–AY (kanan) : TABEL HONORARIUM yang kita inginkan
        //
        // Strategi:
        //  1. Cari header row dari baris 39+ yang memuat PERUNTUKAN + BRUTO/NETTO/PPH
        //  2. Cari posisi kolom PERUNTUKAN/PEJABATAN → startColIdx
        //  3. Cari posisi kolom NETTO → lastColIdx
        //  4. Baca HANYA kolom startColIdx–lastColIdx, baris 39–tableEnd
        //  5. Hentikan di baris footer (Jakarta / Mengetahui / dll)
        // ══════════════════════════════════════════════════════════════════

        // ── Step 1: Cari baris header (mulai dari baris 39, setelah rekap kiri/kanan) ──
        $searchFrom = 39;
        $headerRow = null;

        for ($r = $searchFrom; $r <= min($highestRow, 200); $r++) {
            $rowText = '';
            for ($c = 1; $c <= min($highestColIdx, 120); $c++) {
                $ref = Coordinate::stringFromColumnIndex($c).$r;
                $val = strtoupper(trim((string) $worksheet->getCell($ref)->getFormattedValue()));
                if ($val !== '') {
                    $rowText .= ' '.$val;
                }
            }

            if (str_contains($rowText, 'PERUNTUKAN') || str_contains($rowText, 'PEJABATAN') || str_contains($rowText, 'JABATAN')) {
                // Periksa apakah baris di sekitar (maks 3 baris di atas atau bawah) mengandung keyword finansial
                $foundFinancial = false;
                for ($checkR = max($searchFrom, $r - 3); $checkR <= min($highestRow, $r + 3); $checkR++) {
                    $checkText = '';
                    for ($c = 1; $c <= min($highestColIdx, 120); $c++) {
                        $checkRef = Coordinate::stringFromColumnIndex($c).$checkR;
                        $checkVal = strtoupper(trim((string) $worksheet->getCell($checkRef)->getFormattedValue()));
                        if ($checkVal !== '') {
                            $checkText .= ' '.$checkVal;
                        }
                    }
                    if (str_contains($checkText, 'BRUTO') || str_contains($checkText, 'NETTO') || str_contains($checkText, 'PPH')) {
                        $foundFinancial = true;
                        break;
                    }
                }
                if ($foundFinancial) {
                    $headerRow = $r;
                    break;
                }
            }
        }

        if ($headerRow === null) {
            return [
                'rows' => [],
                'headerRow' => null,
                'dataStart' => null,
                'dataEnd' => null,
                'startColIdx' => 1,
                'lastColIdx' => 1,
                'signatureLines' => [],
            ];
        }

        // ── Step 2: Cari kolom PERUNTUKAN/PEJABATAN → startColIdx ──
        // Scan HANYA dari headerRow ke bawah (bukan ke atas), agar tidak
        // menyentuh baris rekap kiri/kanan (yang berakhir di baris 38).
        $peruntukanCol = null;
        for ($c = 1; $c <= $highestColIdx; $c++) {
            for ($checkRow = $headerRow; $checkRow <= min($highestRow, $headerRow + 3); $checkRow++) {
                $ref = Coordinate::stringFromColumnIndex($c).$checkRow;
                $val = strtoupper(trim((string) $worksheet->getCell($ref)->getFormattedValue()));
                if (
                    str_contains($val, 'PERUNTUKAN')
                    || str_contains($val, 'PEJABATAN')
                    || str_contains($val, 'JABATAN')
                ) {
                    $peruntukanCol = $c;
                    break 2;
                }
            }
        }

        // Cari kolom "NO" di sebelah kiri PERUNTUKAN untuk startColIdx
        $startColIdx = $peruntukanCol;
        if ($peruntukanCol !== null) {
            for ($c = $peruntukanCol - 1; $c >= max(1, $peruntukanCol - 3); $c--) {
                for ($checkRow = $headerRow; $checkRow <= min($highestRow, $headerRow + 3); $checkRow++) {
                    $ref = Coordinate::stringFromColumnIndex($c).$checkRow;
                    $val = strtoupper(trim((string) $worksheet->getCell($ref)->getFormattedValue()));
                    if ($val === 'NO' || $val === 'NO.' || $val === 'NUM') {
                        $startColIdx = $c;
                        break 2;
                    }
                }
            }
        }
        if ($startColIdx === null) {
            $startColIdx = 1;
        }

        // ── Step 3: Cari kolom NETTO → lastColIdx ──
        $nettoCol = null;
        for ($c = $highestColIdx; $c >= $startColIdx; $c--) {
            for ($checkRow = $headerRow; $checkRow <= min($highestRow, $headerRow + 3); $checkRow++) {
                $ref = Coordinate::stringFromColumnIndex($c).$checkRow;
                $val = strtoupper(trim((string) $worksheet->getCell($ref)->getFormattedValue()));
                if (str_contains($val, 'NETTO')) {
                    $nettoCol = $c;
                    break 2;
                }
            }
        }

        if ($nettoCol !== null) {
            $lastColIdx = $nettoCol;
        } else {
            // Fallback: kolom terakhir yang punya isi di headerRow (dari kanan)
            $lastColIdx = $startColIdx;
            for ($c = $highestColIdx; $c >= $startColIdx; $c--) {
                $ref = Coordinate::stringFromColumnIndex($c).$headerRow;
                $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
                if ($val !== '') {
                    $lastColIdx = $c;
                    break;
                }
            }
        }

        // ── Step 4: Tentukan range baris ──
        // tableStart: minimal baris 39, mundur maks 2 baris untuk judul
        $tableStart = max(39, $headerRow - 2);
        $tableEnd = $highestRow;

        $footerKeywords = [
            'jakarta', 'mengetahui', 'kuasa pengelola',
            'bendahara', 'panitera ma-ri', 'petugas pembuat',
        ];
        $signatureLines = [];

        for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
            $rowText = '';
            for ($c = $startColIdx; $c <= $lastColIdx; $c++) {
                $ref = Coordinate::stringFromColumnIndex($c).$r;
                $rowText .= ' '.strtolower(trim((string) $worksheet->getCell($ref)->getFormattedValue()));
            }

            $isFooter = false;
            foreach ($footerKeywords as $kw) {
                if (str_contains($rowText, $kw)) {
                    $isFooter = true;
                    break;
                }
            }

            if ($isFooter) {
                $tableEnd = $r - 1; // Baris data berakhir tepat sebelum footer

                for ($fr = $r; $fr <= min($r + 25, $highestRow); $fr++) {
                    $lineText = '';
                    for ($c = $startColIdx; $c <= $lastColIdx; $c++) {
                        $ref = Coordinate::stringFromColumnIndex($c).$fr;
                        $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
                        if ($val !== '') {
                            $lineText .= $val.' ';
                        }
                    }
                    $lineText = trim($lineText);
                    if ($lineText !== '') {
                        $signatureLines[] = $lineText;
                    }
                }
                break;
            }
        }

        // ── Step 5: Kumpulkan merged cells dalam area tabel ──
        $mergedRanges = [];
        $coveredCells = [];

        foreach ($worksheet->getMergeCells() as $range) {
            [$startCell, $endCell] = explode(':', $range);
            [$sc, $sr] = $this->splitCellReference($startCell);
            [$ec, $er] = $this->splitCellReference($endCell);

            if ($er < $tableStart || $sr > $tableEnd) {
                continue;
            }
            if ($ec < $startColIdx || $sc > $lastColIdx) {
                continue;
            }

            $clampedEr = min($er, $tableEnd);
            $clampedEc = min($ec, $lastColIdx);

            $mergedRanges[$startCell] = [
                'rowspan' => $clampedEr - $sr + 1,
                'colspan' => $clampedEc - $sc + 1,
            ];

            for ($r = $sr; $r <= $clampedEr; $r++) {
                for ($c = $sc; $c <= $clampedEc; $c++) {
                    $ref = Coordinate::stringFromColumnIndex($c).$r;
                    if ($ref !== $startCell) {
                        $coveredCells[$ref] = true;
                    }
                }
            }
        }

        // ── Step 6: Baca baris data ──
        $rows = [];
        for ($row = $tableStart; $row <= $tableEnd; $row++) {
            $cells = [];
            $hasData = false;

            for ($colIdx = $startColIdx; $colIdx <= $lastColIdx; $colIdx++) {
                $cellRef = Coordinate::stringFromColumnIndex($colIdx).$row;

                if (isset($coveredCells[$cellRef])) {
                    continue;
                }

                $cell = $worksheet->getCell($cellRef);
                $value = trim((string) $cell->getFormattedValue());

                try {
                    $rawValue = $cell->getCalculatedValue();
                } catch (\Throwable $e) {
                    $rawValue = $cell->getValue();
                }
                $rawStr = trim((string) ($rawValue ?? ''));

                if ($rawStr !== '' && $rawStr !== '0' && $rawStr !== '-' && ! (is_numeric($rawStr) && (float) $rawStr == 0)) {
                    $hasData = true;
                }

                $cells[] = [
                    'reference' => $cellRef,
                    'value' => $value,
                    'rowspan' => $mergedRanges[$cellRef]['rowspan'] ?? 1,
                    'colspan' => $mergedRanges[$cellRef]['colspan'] ?? 1,
                ];
            }

            $rows[] = [
                'number' => $row,
                'cells' => $cells,
                'hasData' => $hasData,
            ];
        }

        return [
            'rows' => $rows,
            'headerRow' => $headerRow,
            'dataStart' => $headerRow + 1,
            'dataEnd' => $tableEnd,
            'startColIdx' => $startColIdx,
            'lastColIdx' => $lastColIdx,
            'signatureLines' => $signatureLines,
        ];
    }

    public function deleteFile($id)
    {
        try {
            $excelFile = ExcelFile::findOrFail($id);
            $resolvedPath = $this->resolveExcelFilePath($excelFile->file_path);
            if ($resolvedPath && file_exists($resolvedPath)) {
                unlink($resolvedPath);
            }
            $excelFile->delete();
            if (Session::get('current_file_id') == (int) $id) {
                $this->invalidateSessionCache();
                Session::forget(['current_file_id', 'excel_file_name', 'excel_file_path', 'excel_sheets', 'excel_period']);
            }

            return response()->json(['success' => true, 'message' => 'File berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 400);
        }
    }

    private function resolveExcelFilePath(?string $storedPath): ?string
    {
        if (! $storedPath) {
            return null;
        }

        if (file_exists($storedPath)) {
            return $storedPath;
        }

        $normalizedPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $storedPath);
        $candidates = [
            storage_path('app/'.ltrim($normalizedPath, DIRECTORY_SEPARATOR)),
            storage_path('app/uploads/'.basename($normalizedPath)),
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function renamePeriod(Request $request, $id)
    {
        try {
            $request->validate(['period' => 'required|string|max:100']);
            $excelFile = ExcelFile::findOrFail($id);
            $excelFile->update(['period' => $request->input('period')]);
            if (Session::get('current_file_id') == (int) $id) {
                Session::put('excel_period', $request->input('period'));
            }

            return response()->json(['success' => true, 'message' => 'Periode berhasil diubah']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 400);
        }
    }
}
