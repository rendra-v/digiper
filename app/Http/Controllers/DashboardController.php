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

            if (! file_exists($excelFile->file_path)) {
                return redirect('/dashboard')->with('error', 'File tidak ditemukan');
            }

            // Hapus cache lama jika file berbeda
            $oldPath = Session::get('excel_file_path', '');
            if ($oldPath !== $excelFile->file_path) {
                $this->invalidateSessionCache();
            }

            Session::put('current_file_id', $excelFile->id);
            Session::put('excel_file_name', $excelFile->original_filename);
            Session::put('excel_file_path', $excelFile->file_path);
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
            $reader = IOFactory::createReaderForFile($excelFile->file_path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($excelFile->file_path);

            $sheetNames = $spreadsheet->getSheetNames();

            // Hapus cache lama sebelum set file baru
            $this->invalidateSessionCache();

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
                'file_path' => $fullPath,
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

                if ($rawStr !== '' && $rawStr !== '0' && $rawStr !== '-' && !(is_numeric($rawStr) && (float) $rawStr == 0)) {
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
                if ($rawStr !== '' && $rawStr !== '0' && $rawStr !== '-' && !(is_numeric($rawStr) && (float) $rawStr == 0)) {
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

            // Cek cache
            $cacheKey = $this->getCacheKey($filePath, 'honorarium');
            $cached = Session::get($cacheKey);
            if ($cached !== null) {
                \Log::info('honorarium() - loaded from session cache');

                return view('honorarium', array_merge($cached, [
                    'fileName' => $fileName,
                    'error' => null,
                ]));
            }

            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load($filePath);

            $allSheetNames = $spreadsheet->getSheetNames();

            // Cari sheet yang mengandung kata honorarium/honor
            $honorSheets = array_values(array_filter($allSheetNames, function ($name) {
                $lower = strtolower($name);

                return str_contains($lower, 'honor') || str_contains($lower, 'honorarium');
            }));

            // Jika tidak ada, ambil semua sheet kecuali yang sudah dipakai
            if (empty($honorSheets)) {
                $usedSheets = ['Data Print', 'cek', 'Rekap Keseluruhan', 'REKAP GABUNGAN', 'rekap keseluruhan', 'Periode Laporan'];
                $honorSheets = array_values(array_filter($allSheetNames, fn ($n) => ! in_array($n, $usedSheets)));
            }

            $sheets = [];
            foreach ($honorSheets as $sheetName) {
                $ws = $spreadsheet->getSheetByName($sheetName);
                if (! $ws) {
                    continue;
                }

                $parsed = $this->parseHonorariumSheet($ws, $sheetName);
                if ($parsed !== null) {
                    $sheets[] = $parsed;
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

            $honorSheets = array_values(array_filter($allSheetNames, function ($name) {
                $lower = strtolower($name);

                return str_contains($lower, 'honor') || str_contains($lower, 'honorarium');
            }));
            if (empty($honorSheets)) {
                $usedSheets = ['Data Print', 'cek', 'Rekap Keseluruhan', 'REKAP GABUNGAN', 'rekap keseluruhan', 'Periode Laporan'];
                $honorSheets = array_values(array_filter($allSheetNames, fn ($n) => ! in_array($n, $usedSheets)));
            }

            $sheets = [];
            foreach ($honorSheets as $sheetName) {
                $ws = $spreadsheet->getSheetByName($sheetName);
                if (! $ws) {
                    continue;
                }
                $parsed = $this->parseHonorariumSheet($ws, $sheetName);
                if ($parsed !== null) {
                    $sheets[] = $parsed;
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
     * Mencari baris header (mengandung kolom NO/NAMA/JABATAN atau serupa),
     * lalu membaca data dan mengembalikan array terstruktur.
     */
    private function parseHonorariumSheet($worksheet, string $sheetName): ?array
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColIdx = Coordinate::columnIndexFromString($highestColumn);

        // Ambil judul (beberapa baris pertama sebelum header)
        $titleLines = [];
        $headerRow = null;

        for ($r = 1; $r <= min($highestRow, 20); $r++) {
            $rowHasNo = false;
            $rowHasNama = false;
            $rowText = '';

            for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
                $ref = Coordinate::stringFromColumnIndex($c).$r;
                $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
                if ($val === '') {
                    continue;
                }
                $upper = strtoupper($val);
                $rowText .= ' '.$val;
                if ($upper === 'NO' || $upper === 'NO.' || $upper === 'NOMOR') {
                    $rowHasNo = true;
                }
                if (str_contains($upper, 'NAMA')) {
                    $rowHasNama = true;
                }
            }

            if ($rowHasNo && $rowHasNama) {
                $headerRow = $r;
                break;
            }

            $text = trim($rowText);
            if ($text !== '') {
                $titleLines[] = $text;
            }
        }

        if ($headerRow === null) {
            return null;
        }

        // Baca kolom header
        $headers = [];
        for ($c = 1; $c <= $highestColIdx; $c++) {
            $ref = Coordinate::stringFromColumnIndex($c).$headerRow;
            // Cek merged cells — bisa saja header ada di baris headerRow-1 juga
            $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
            // Juga cek baris di atasnya jika kosong (untuk header bertingkat)
            if ($val === '' && $headerRow > 1) {
                $refAbove = Coordinate::stringFromColumnIndex($c).($headerRow - 1);
                $valAbove = trim((string) $worksheet->getCell($refAbove)->getFormattedValue());
                if ($valAbove !== '') {
                    $val = $valAbove;
                }
            }
            $headers[$c] = $val !== '' ? $val : Coordinate::stringFromColumnIndex($c);
        }

        // Baca data
        $rows = [];
        for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
            $rowData = [];
            $hasData = false;
            $firstVal = trim((string) $worksheet->getCell('A'.$r)->getFormattedValue());

            // Stop jika baris ini mengandung teks tanda tangan / footer
            if (stripos($firstVal, 'Jakarta') !== false
                || stripos($firstVal, 'Mengetahui') !== false
                || stripos($firstVal, 'PANITERA') !== false
            ) {
                break;
            }

            for ($c = 1; $c <= $highestColIdx; $c++) {
                $ref = Coordinate::stringFromColumnIndex($c).$r;
                $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
                $key = $headers[$c] ?? Coordinate::stringFromColumnIndex($c);
                $rowData[$key] = $val;
                if ($val !== '') {
                    $hasData = true;
                }
            }

            if ($hasData) {
                // Skip baris total/sub-total yang tidak bernomor
                $noVal = trim((string) ($rowData[$headers[1] ?? 'A'] ?? ''));
                if ($noVal !== '' && ! is_numeric($noVal) && strtoupper($noVal) !== 'NO') {
                    // Ini baris sub-total/jumlah, tetap tampilkan
                }
                $rows[] = $rowData;
            }
        }

        if (empty($rows)) {
            return null;
        }

        return [
            'sheetName' => $sheetName,
            'title' => implode("\n", array_slice($titleLines, 0, 5)),
            'headers' => $headers,
            'rows' => $rows,
        ];
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

                if ($rawStr !== '' && $rawStr !== '0' && $rawStr !== '-' && !(is_numeric($rawStr) && (float) $rawStr == 0)) {
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
            if (file_exists($excelFile->file_path)) {
                unlink($excelFile->file_path);
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
