<?php
/**
 * Fix parseHonorariumSheet to MERGE all sub-tables in a sheet into one unified table
 * with section separators, instead of creating 27 separate tabs.
 */

$filePath = __DIR__ . '/app/Http/Controllers/DashboardController.php';
$content = file_get_contents($filePath);

$funcStart = strpos($content, '    private function parseHonorariumSheet');
$nextFuncStart = strpos($content, '    private function parseFooterBlocks', $funcStart);
if ($funcStart === false || $nextFuncStart === false) {
    die("ERROR: Markers not found\n");
}
$docblockStart = strrpos(substr($content, 0, $nextFuncStart), '    /**');
if ($docblockStart !== false && $docblockStart > $funcStart) {
    $endPos = $docblockStart;
} else {
    $endPos = $nextFuncStart;
}

echo "Replacing from char $funcStart to $endPos\n";

$newFunction = <<<'PHPCODE'
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

        foreach ($headerRows as $idx => $hRow) {
            // Batas akhir chunk ini
            if ($idx < count($headerRows) - 1) {
                $nextHRow = $headerRows[$idx + 1];
                // Mundur ke awal title berikutnya
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
                        break;
                    }
                }
                $endBoundary = $titleStart - 1;
            } else {
                $endBoundary = $highestRow;
            }

            // Ambil judul sub-tabel (dari baris sebelum hRow, tapi sesudah chunk sebelumnya)
            $subTitleStart = ($idx === 0) ? 1 : $headerRows[$idx - 1]; // Tidak sempurna, tapi ok
            // Lebih baik: ambil judul dari baris yang ada text sebelum hRow
            $subTitleLines = [];
            // Cari batas atas sub-judul: scan mundur dari hRow-1 sampai ketemu baris kosong
            $scanStart = $hRow - 1;
            $titleBoundary = $scanStart;
            for ($r = $hRow - 1; $r >= 1; $r--) {
                $hasText = false;
                for ($c = 1; $c <= min($highestColIdx, 12); $c++) {
                    if (trim((string) $worksheet->getCell([$c, $r])->getFormattedValue()) !== '') {
                        $hasText = true;
                        break;
                    }
                }
                if ($hasText) {
                    $titleBoundary = $r;
                } else {
                    break;
                }
            }
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

            $subTitle = implode(' | ', $subTitleLines);

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

PHPCODE;

$before = substr($content, 0, $funcStart);
$after  = substr($content, $endPos);
$newContent = $before . $newFunction . $after;

file_put_contents($filePath, $newContent);
echo "SUCCESS: parseHonorariumSheet replaced (v2 - merge all subtables).\n";
echo "New file size: " . strlen($newContent) . " bytes\n";
