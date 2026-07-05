<?php
/**
 * Script untuk memperbaiki DashboardController.php:
 * - Mengganti parseHonorariumSheet yang rusak dengan versi baru yang bisa handle multiple tables per sheet
 */

$filePath = __DIR__ . '/app/Http/Controllers/DashboardController.php';
$content = file_get_contents($filePath);

// === Temukan batas START function parseHonorariumSheet ===
$funcStart = strpos($content, '    private function parseHonorariumSheet');
if ($funcStart === false) {
    die("ERROR: Could not find parseHonorariumSheet start\n");
}

// === Temukan batas AKHIR function (closing brace) ===
// Cari "    private function parseFooterBlocks" karena itu function berikutnya
$nextFuncStart = strpos($content, '    private function parseFooterBlocks', $funcStart);
if ($nextFuncStart === false) {
    die("ERROR: Could not find parseFooterBlocks (next function)\n");
}

// Find docblock sebelum parseFooterBlocks
$docblockStart = strrpos(substr($content, 0, $nextFuncStart), '    /**');
if ($docblockStart !== false && $docblockStart > $funcStart) {
    $endPos = $docblockStart;
} else {
    $endPos = $nextFuncStart;
}

echo "parseHonorariumSheet starts at char: $funcStart\n";
echo "Will cut up to char: $endPos\n";
echo "Removed chars: " . ($endPos - $funcStart) . "\n";

// === New function body ===
$newFunction = <<<'PHPCODE'
    /**
     * Parse sebuah sheet honorarium.
     * Mendukung multiple tabel dalam satu sheet.
     * Mengembalikan array of tables (bukan single table).
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
                $val   = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
                if ($val === '') {
                    continue;
                }
                $upper = strtoupper($val);
                if ($upper === 'NO' || $upper === 'NO.' || $upper === 'NOMOR') {
                    $rowHasNo = true;
                }
                if (str_contains($upper, 'NAMA')) {
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

        // ── 2. Proses setiap tabel secara terpisah ───────────────────────────
        $tables        = [];
        $startBoundary = 1;

        foreach ($headerRows as $idx => $hRow) {
            // Tentukan batas akhir baris untuk tabel ini
            if ($idx < count($headerRows) - 1) {
                $nextHRow = $headerRows[$idx + 1];
                // Mundur dari nextHRow untuk menemukan awal title berikutnya
                $titleStart = $nextHRow;
                for ($r = $nextHRow - 1; $r > $hRow + 1; $r--) {
                    $hasText = false;
                    for ($c = 1; $c <= min($highestColIdx, 12); $c++) {
                        if (trim((string) $worksheet->getCell([$c, $r])->getFormattedValue()) !== '') {
                            $hasText = true;
                            break;
                        }
                    }
                    if ($hasText) {
                        $titleStart = $r;
                    } else {
                        break; // Baris kosong = batas atas judul berikutnya
                    }
                }
                $endBoundary = $titleStart - 1;
            } else {
                $endBoundary = $highestRow;
            }

            // ── 2a. Baca baris judul (dari startBoundary s/d hRow-1) ─────────
            $titleLines = [];
            for ($r = $startBoundary; $r < $hRow; $r++) {
                $rowText = '';
                for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
                    $val = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
                    if ($val !== '') {
                        $rowText .= ' ' . $val;
                    }
                }
                $text = trim($rowText);
                if ($text !== '') {
                    $titleLines[] = $text;
                }
            }

            // ── 2b. Baca kolom header ────────────────────────────────────────
            $headers = [];
            for ($c = 1; $c <= $highestColIdx; $c++) {
                $val = trim((string) $worksheet->getCell([$c, $hRow])->getFormattedValue());
                if ($val === '' && $hRow > 1) {
                    $valAbove = trim((string) $worksheet->getCell([$c, $hRow - 1])->getFormattedValue());
                    if ($valAbove !== '') {
                        $val = $valAbove;
                    }
                }
                $headers[$c] = $val !== '' ? $val : Coordinate::stringFromColumnIndex($c);
            }

            // ── 2c. Baca baris data dan footer ──────────────────────────────
            $colHasContent = array_fill(1, $highestColIdx, false);
            $rows          = [];
            $footerRows    = [];
            $inFooter      = false;
            $footerKeywords = [
                'jakarta', 'mengetahui', 'panitera mahkamah', 'petugas pembuat',
                'bendahara', 'kuasa pengelola', 'biaya proses',
            ];

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
                    $fData    = [];
                    $fHasData = false;
                    for ($c = 1; $c <= $highestColIdx; $c++) {
                        $val      = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
                        $fData[$c] = $val;
                        if ($val !== '') {
                            $fHasData = true;
                        }
                    }
                    if ($fHasData) {
                        $footerRows[] = $fData;
                    }
                } else {
                    $rowData    = [];
                    $rowHasData = false;
                    for ($c = 1; $c <= $highestColIdx; $c++) {
                        // Gunakan calculated value (untuk formula)
                        $cell = $worksheet->getCell([$c, $r]);
                        try {
                            $cell->getCalculatedValue();
                        } catch (\Throwable $e) {
                            // abaikan error kalkulasi formula
                        }
                        $val             = trim((string) $cell->getFormattedValue());
                        $rowData[$headers[$c]] = $val;
                        if ($val !== '') {
                            $rowHasData = true;
                            $colHasContent[$c] = true;
                        }
                    }
                    if ($rowHasData) {
                        $rows[] = $rowData;
                    }
                }
            }

            // ── 2d. Hapus kolom sepenuhnya kosong ───────────────────────────
            $activeHeaders = [];
            foreach ($headers as $c => $hName) {
                if ($colHasContent[$c]) {
                    $activeHeaders[$c] = $hName;
                }
            }

            // ── 2e. Filter row agar hanya kolom aktif ───────────────────────
            $filteredRows = array_map(function ($row) use ($activeHeaders) {
                $out = [];
                foreach ($activeHeaders as $hName) {
                    $out[$hName] = $row[$hName] ?? '';
                }
                return $out;
            }, $rows);

            // ── 2f. Deteksi kolom jabatan & auto-koding kode_kuitansi ────────
            $timKeywords = [
                'hakim agung', 'hakim', 'askor', 'asisten koordinator',
                'panitera muda', 'panmud', 'asisten', 'operator', 'tim korektor',
            ];
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

            // Strategi 2: scan VALUES semua kolom teks
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

            // ── 2g. Parse footer blok tanda-tangan ──────────────────────────
            $footerBlocks = $this->parseFooterBlocks($footerRows, $headers, $highestColIdx);

            $tables[] = [
                'sheetName'     => $sheetName,
                'title'         => implode("\n", array_slice($titleLines, 0, 5)),
                'headers'       => $activeHeaders,
                'rows'          => $filteredRows,
                'footerBlocks'  => $footerBlocks,
                'jabatanColName' => $jabatanColName,
            ];

            $startBoundary = $endBoundary + 1;
        }

        return $tables;
    }

PHPCODE;

// === Lakukan penggantian ===
$before = substr($content, 0, $funcStart);
$after  = substr($content, $endPos);
$newContent = $before . $newFunction . $after;

file_put_contents($filePath, $newContent);
echo "SUCCESS: parseHonorariumSheet replaced.\n";
echo "New file size: " . strlen($newContent) . " bytes\n";

// === Sekarang periksa/fix pemanggil di honorarium() ===
$content2 = file_get_contents($filePath);

// Cari pola lama
$oldPattern = '$parsed = $this->parseHonorariumSheet($ws, $sheetName);
                if ($parsed !== null) {
                    $sheets[] = $parsed;
                }';

$newPattern = '$parsedTables = $this->parseHonorariumSheet($ws, $sheetName);
                if (!empty($parsedTables)) {
                    foreach ($parsedTables as $tblIdx => $parsedTable) {
                        if (count($parsedTables) > 1) {
                            $parsedTable[\'sheetName\'] = $sheetName . \' (Bagian \' . ($tblIdx + 1) . \')\';
                        }
                        $sheets[] = $parsedTable;
                    }
                }';

if (strpos($content2, $oldPattern) !== false) {
    $content2 = str_replace($oldPattern, $newPattern, $content2);
    file_put_contents($filePath, $content2);
    echo "SUCCESS: honorarium() caller updated.\n";
} else {
    // Try to find alternate pattern
    echo "WARNING: Old pattern not found literally, searching for parseHonorariumSheet call...\n";
    preg_match('/\$parsed = \$this->parseHonorariumSheet[^;]+;[^}]+\$sheets\[\] = \$parsed;[^}]+}/ms', $content2, $matches);
    if ($matches) {
        echo "FOUND:\n" . $matches[0] . "\n";
    } else {
        // Try simpler
        preg_match('/parseHonorariumSheet\(\$ws[^;]+;/', $content2, $m2);
        if ($m2) echo "FOUND SIMPLE: " . $m2[0] . "\n";
    }
}
