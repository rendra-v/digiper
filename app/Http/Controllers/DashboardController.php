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
                'file' => 'required|file|mimes:xlsx,xls,xlsm,xlsb,csv|max:'.$maxUploadKb,
                'period' => 'required|string|max:100',
            ], [
                'file.required' => 'File harus diupload',
                'file.mimes' => 'Format file harus Excel (.xlsx, .xls, .xlsm, .xlsb, .csv)',
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
            ini_set('memory_limit', '1024M');
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
            ini_set('memory_limit', '1024M');
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
            ini_set('memory_limit', '1024M');
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
                'file' => 'required|file|mimes:xlsx,xls,xlsm,xlsb,csv|max:'.$maxUploadKb,
            ], [
                'file.required' => 'File harus diupload',
                'file.mimes' => 'Format file harus Excel (.xlsx, .xls, .xlsm, .xlsb, .csv)',
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
            ini_set('memory_limit', '1024M');

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
            $reader->setReadDataOnly(false);
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
            ini_set('memory_limit', '1024M');
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
                    if (in_array($col, $excludedColumns)) continue;
                    $val = $getCellValue($worksheet->getCell($col.$row));
                    if ($val && (strtoupper(trim((string)$val)) === 'NO' || stripos(trim((string)$val), 'nomor') !== false)) {
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
            
            for ($colIndex = 1; $colIndex <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); $colIndex++) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                if (in_array($col, $excludedColumns)) continue;

                $cell = $worksheet->getCell($col.$headerRowNum);
                $headerValue = $getCellValue($cell);
                
                $name = $headerValue ? trim((string)$headerValue) : '';
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
                        if ($totalAfterPajakCount === 1) $key = 'TOTAL_1';
                        elseif ($totalAfterPajakCount === 2) $key = 'TOTAL_2';
                        elseif ($totalAfterPajakCount === 3) $key = 'TOTAL_3';
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
                    list($start, $end) = explode(':', $range);
                    $startCol = preg_replace('/[0-9]/', '', $start);
                    $startRow = (int)preg_replace('/[A-Z]/', '', $start);
                    $endCol = preg_replace('/[0-9]/', '', $end);
                    $endRow = (int)preg_replace('/[A-Z]/', '', $end);
                    
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
                        if ($key === 'BIAYA' && (int)$value === 250000) $isOpeningKasasi = true;
                        if ($key === 'TIM') $foundTim = true;
                        if ($foundTim && !in_array($key, ['TIM', 'TOTAL_1', 'TOTAL_2', 'TOTAL_3'])) $onlyFilledUpToTim = false;
                    }
                }

                if ($hasData) {
                    if ($isOpeningKasasi || ($foundTim && $onlyFilledUpToTim)) {
                        $rowData['TOTAL_1'] = null;
                        $rowData['TOTAL_2'] = null;
                        if (!isset($rowspans['TOTAL_3'])) {
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
                        if (is_numeric($comp)) $sum += (float)$comp;
                    }
                    if ($sum > 0) $r['TOTAL_2'] = $sum;
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
                        if ($comp !== 'SKIP_OR_NULL' && is_numeric($comp)) $sum += (float)$comp;
                    }
                    if ($sum > 0) $r['TOTAL_3'] = $sum;
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
                        $rowText .= ' ' . (string)$v;
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
                
                if ($isFooter) $footerData[] = $row;
                else $tableData[] = $row;
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
            for ($column = 1; $column <= $lastColumnIndex; $column++) {
                $cellReference = Coordinate::stringFromColumnIndex($column).$row;

                if (isset($coveredCells[$cellReference])) {
                    continue;
                }

                $cell = $worksheet->getCell($cellReference);
                $value = trim((string) $cell->getFormattedValue());

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
            try {
                $value = $cell->getCalculatedValue();
                if (is_string($value) && strpos($value, '=') === 0) return null;
                return $value;
            } catch (\Exception $e) {
                $val = $cell->getValue();
                if (is_string($val) && strpos($val, '=') === 0) return null;
                return $val;
            }
        };

        $isValidDataRow = function ($rowData, $currentHeaders) {
            $firstCell = null;
            foreach ($currentHeaders as $col => $header) {
                if ($header === 'No' && isset($rowData[$header])) {
                    $firstCell = $rowData[$header];
                    break;
                }
            }
            if (! $firstCell) return false; 
            $firstCellStr = trim((string) $firstCell);
            if (strtoupper($firstCellStr) === 'NO' || strtoupper($firstCellStr) === 'NUMBER') return false;
            if (stripos($firstCellStr, 'PERKARA') !== false || stripos($firstCellStr, 'TOTAL') !== false || stripos($firstCellStr, 'DATA') !== false || strpos($firstCellStr, '~') !== false) return false;
            if (! is_numeric($firstCellStr) && ! ctype_digit($firstCellStr)) return false;
            $meaningfulCount = 0;
            foreach ($rowData as $value) {
                $val = trim((string) $value);
                if ($val !== '' && $val !== '-' && $val !== '~') $meaningfulCount++;
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
                    if ($value !== null && $value !== '') $hasData = true;
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
                            if ($cIdx === 2) { $secondCol = $h; break; }
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
                    if (is_string($value) && strpos($value, '=') === 0) return null;
                    return $value;
                } catch (\Exception $e) {
                    $val = $cell->getValue();
                    if (is_string($val) && strpos($val, '=') === 0) return null;
                    return $val;
                }
            };

            $headerRow = 1;
            for ($row = 1; $row <= min(20, $highestRow); $row++) {
                $foundNo = false;
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $val = $getCellValue($worksheet->getCell($col.$row));
                    if ($val && (strtoupper(trim((string)$val)) === 'NO' || stripos(trim((string)$val), 'nomor') !== false)) {
                        $headerRow = $row;
                        $foundNo = true;
                        break;
                    }
                }
                if ($foundNo) break;
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
                    if ($value !== null && $value !== '') $hasData = true;
                }
                if ($hasData) $data[] = $rowData;
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
        if ($uploadKb <= 0 || $postKb <= 0) return self::APP_MAX_UPLOAD_KB;
        return min($uploadKb, $postKb);
    }

    private function iniSizeToKb(string $value): int
    {
        $trimmed = trim($value);
        if ($trimmed === '') return 0;
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

    public function rekapKeseluruhan()
    {
        try {
            ini_set('memory_limit', '1024M');
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-keseluruhan', [
                    'fileName'  => null,
                    'tableData' => [],
                    'title1'    => '',
                    'title2'    => '',
                    'recapDate' => '',
                    'error'     => 'File tidak ditemukan. Silakan upload file terlebih dahulu.',
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
                return view('rekap-keseluruhan', [
                    'fileName'  => $fileName,
                    'tableData' => [],
                    'title1'    => '',
                    'title2'    => '',
                    'recapDate' => '',
                    'error'     => 'Sheet "Rekap Keseluruhan" tidak ditemukan dalam file.',
                ]);
            }

            $report = $this->buildRekapKeseluruhanReport($sheet);

            // Get title lines
            $rows = collect($report['rows'])->keyBy('number');
            $getCellVal = function (int $rowNum, string $ref, string $default = '') use ($rows) {
                $row  = $rows->get($rowNum);
                $cell = $row ? collect($row['cells'] ?? [])->firstWhere('reference', $ref) : null;
                return $cell['value'] ?? $default;
            };

            $title1 = $getCellVal(2, 'A2', 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS');
            $title2 = $getCellVal(3, 'A3', 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK');

            $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
            $recapDate   = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : 'Jakarta, 05 Maret 2026';

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return view('rekap-keseluruhan', [
                'fileName'  => $fileName,
                'report'    => $report,
                'title1'    => $title1,
                'title2'    => $title2,
                'recapDate' => $recapDate,
                'error'     => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in rekapKeseluruhan', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            return view('rekap-keseluruhan', [
                'fileName'  => Session::get('excel_file_name'),
                'tableData' => [],
                'title1'    => '',
                'title2'    => '',
                'recapDate' => '',
                'error'     => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function rekapKeseluruhanPrint()
    {
        try {
            ini_set('memory_limit', '1024M');
            $filePath = Session::get('excel_file_path');
            $fileName = Session::get('excel_file_name');

            if (! $filePath || ! file_exists($filePath)) {
                return view('rekap-keseluruhan-print', [
                    'fileName'  => null,
                    'report'    => ['rows' => []],
                    'title1'    => '',
                    'title2'    => '',
                    'recapDate' => '',
                    'error'     => 'File tidak ditemukan.',
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
                return view('rekap-keseluruhan-print', [
                    'fileName'  => $fileName,
                    'report'    => ['rows' => []],
                    'title1'    => '',
                    'title2'    => '',
                    'recapDate' => '',
                    'error'     => 'Sheet "Rekap Keseluruhan" tidak ditemukan.',
                ]);
            }

            $report = $this->buildRekapKeseluruhanReport($sheet);

            $rows = collect($report['rows'])->keyBy('number');
            $getCellVal = function (int $rowNum, string $ref, string $default = '') use ($rows) {
                $row  = $rows->get($rowNum);
                $cell = $row ? collect($row['cells'] ?? [])->firstWhere('reference', $ref) : null;
                return $cell['value'] ?? $default;
            };

            $title1 = $getCellVal(2, 'A2', 'REKAPITULASI BIAYA PENYELESAIAN PERKARA YANG DIPUTUS');
            $title2 = $getCellVal(3, 'A3', 'YANG USIANYA KURANG DARI 120 HARI SEJAK REGISTER PERKARA MASUK');

            $periodSheet = $spreadsheet->getSheetByName('Periode Laporan');
            $recapDate   = $periodSheet ? trim((string) $periodSheet->getCell('D7')->getFormattedValue()) : 'Jakarta, 05 Maret 2026';

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            return view('rekap-keseluruhan-print', [
                'fileName'  => $fileName,
                'report'    => $report,
                'title1'    => $title1,
                'title2'    => $title2,
                'recapDate' => $recapDate,
                'error'     => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in rekapKeseluruhanPrint', ['error' => $e->getMessage()]);
            return view('rekap-keseluruhan-print', [
                'fileName'  => Session::get('excel_file_name'),
                'report'    => ['rows' => []],
                'title1'    => '',
                'title2'    => '',
                'recapDate' => '',
                'error'     => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function deleteFile($id)
    {
        try {
            $excelFile = ExcelFile::findOrFail($id);
            if (file_exists($excelFile->file_path)) unlink($excelFile->file_path);
            $excelFile->delete();
            if (Session::get('current_file_id') === $id) {
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
            if (Session::get('current_file_id') === $id) Session::put('excel_period', $request->input('period'));
            return response()->json(['success' => true, 'message' => 'Periode berhasil diubah']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 400);
        }
    }
}
