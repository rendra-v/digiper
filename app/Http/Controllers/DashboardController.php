<?php

namespace App\Http\Controllers;

use App\Models\Perkara;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function index()
    {
        $perkaras = Session::get('excel_data', []);
        $sheets = Session::get('excel_sheets', []);
        $currentSheet = Session::get('excel_current_sheet', '');
        
        return view('dashboard', [
            'perkaras' => $perkaras,
            'sheets' => $sheets,
            'currentSheet' => $currentSheet,
        ]);
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
            
            // Store uploaded file temporarily
            $uploadDir = storage_path('app/uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filePath = $file->store('uploads');
            $fullPath = storage_path('app/' . $filePath);
            
            // Load spreadsheet with optimizations
            $reader = IOFactory::createReaderForFile($file->getPathname());
            $reader->setReadDataOnly(true); // Only read data, skip formatting
            $spreadsheet = $reader->load($file->getPathname());
            
            // Get all sheet names
            $sheetNames = $spreadsheet->getSheetNames();
            \Log::info('Available sheets', ['sheets' => $sheetNames]);
            
            // Try to find "Data Print" sheet, otherwise use first sheet
            $targetSheetName = 'Data Print';
            if (!in_array($targetSheetName, $sheetNames)) {
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
                $cellValue = $worksheet->getCell('A' . $row)->getValue();
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
                $cell = $worksheet->getCell($col . $headerRow);
                $headers[$col] = trim($cell->getValue() ?: $col);
            }
            
            // Read data rows (starting after header)
            for ($row = $headerRow + 1; $row <= $maxRows; $row++) {
                $rowData = [];
                $hasData = false;
                
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cell = $worksheet->getCell($col . $row);
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
                'message' => 'File berhasil diupload! Total: ' . count($data) . ' baris',
                'count' => count($data),
            ]);
            
        } catch (ValidationException $e) {
            \Log::warning('Validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Upload error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
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

    public function getSheet($sheetName)
    {
        try {
            $sheets = Session::get('excel_sheets', []);
            $filePath = Session::get('excel_file_path');
            
            if (!in_array($sheetName, $sheets)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sheet tidak ditemukan',
                ], 404);
            }

            if (!$filePath || !file_exists($filePath)) {
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
                $cellValue = $worksheet->getCell('A' . $row)->getValue();
                if (strtoupper($cellValue) === 'NO' || stripos($cellValue, 'nomor') !== false) {
                    $headerRow = $row;
                    break;
                }
            }
            
            // Get header row
            $headers = [];
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $cell = $worksheet->getCell($col . $headerRow);
                $headers[$col] = trim($cell->getValue() ?: $col);
            }
            
            // Read data rows
            $maxRows = min($highestRow, 50000);
            for ($row = $headerRow + 1; $row <= $maxRows; $row++) {
                $rowData = [];
                $hasData = false;
                
                for ($col = 'A'; $col <= $highestColumn; $col++) {
                    $cell = $worksheet->getCell($col . $row);
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
                'message' => 'Error: ' . $e->getMessage(),
            ], 400);
        }
    }
}
