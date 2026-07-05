<?php
$file = 'f:\digiper\app\Http\Controllers\DashboardController.php';
$content = file_get_contents($file);

$startMarker = '    private function parseHonorariumSheet($worksheet, string $sheetName): ?array';
$endMarker = '    private function calculateSheetSummary(array $sheets): array';

$startPos = strpos($content, $startMarker);
$endPos = strpos($content, $endMarker);

if ($startPos === false || $endPos === false) {
    echo "Markers not found!\n";
    exit(1);
}

// Ensure we get the docblock above calculateSheetSummary
$endBlockStart = strrpos(substr($content, 0, $endPos), '    /**');
if ($endBlockStart !== false && $endBlockStart > $startPos) {
    $endPos = $endBlockStart;
}

$newFunction = <<<'EOD'
    private function parseHonorariumSheet($worksheet, string $sheetName): ?array
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $headerRows = [];
        for ($r = 1; $r <= $highestRow; $r++) {
            $rowHasNo = false;
            $rowHasNama = false;
            for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
                $ref = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c) . $r;
                $val = trim((string) $worksheet->getCell($ref)->getFormattedValue());
                if ($val === '') continue;
                $upper = strtoupper($val);
                if ($upper === 'NO' || $upper === 'NO.' || $upper === 'NOMOR') $rowHasNo = true;
                if (str_contains($upper, 'NAMA')) $rowHasNama = true;
            }
            if ($rowHasNo && $rowHasNama) {
                $headerRows[] = $r;
            }
        }

        if (empty($headerRows)) {
            return null;
        }

        $tables = [];
        $startBoundary = 1;

        foreach ($headerRows as $idx => $hRow) {
            $endBoundary = ($idx < count($headerRows) - 1) ? $headerRows[$idx + 1] - 1 : $highestRow;
            if ($idx < count($headerRows) - 1) {
                $nextHRow = $headerRows[$idx + 1];
                $titleStart = $nextHRow;
                for ($r = $nextHRow - 1; $r > $hRow; $r--) {
                    $hasText = false;
                    for ($c = 1; $c <= min($highestColIdx, 12); $c++) {
                        if (trim((string)$worksheet->getCell([$c, $r])->getFormattedValue()) !== '') {
                            $hasText = true; break;
                        }
                    }
                    if ($hasText) {
                        $titleStart = $r;
                    } else {
                        break;
                    }
                }
                $endBoundary = $titleStart - 1;
            }

            $titleLines = [];
            for ($r = $startBoundary; $r < $hRow; $r++) {
                $rowText = '';
                for ($c = 1; $c <= min($highestColIdx, 20); $c++) {
                    $val = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
                    if ($val !== '') $rowText .= ' ' . $val;
                }
                $text = trim($rowText);
                if ($text !== '') $titleLines[] = $text;
            }

            $headers = [];
            for ($c = 1; $c <= $highestColIdx; $c++) {
                $val = trim((string) $worksheet->getCell([$c, $hRow])->getFormattedValue());
                if ($val === '' && $hRow > 1) {
                    $valAbove = trim((string) $worksheet->getCell([$c, $hRow - 1])->getFormattedValue());
                    if ($valAbove !== '') $val = $valAbove;
                }
                $headers[$c] = $val !== '' ? $val : \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            }

            $colHasContent = array_fill(1, $highestColIdx, false);
            $rows = [];
            $footerRows = [];
            $inFooter = false;
            $footerKeywords = ['jakarta', 'mengetahui', 'panitera mahkamah', 'petugas pembuat', 'bendahara', 'kuasa pengelola', 'biaya proses'];

            for ($r = $hRow + 1; $r <= $endBoundary; $r++) {
                if (! $inFooter) {
                    $rowTextLow = '';
                    for ($c = 1; $c <= min($highestColIdx, 12); $c++) {
                        $rowTextLow .= ' '.strtolower(trim((string) $worksheet->getCell([$c, $r])->getFormattedValue()));
                    }
                    foreach ($footerKeywords as $kw) {
                        if (str_contains($rowTextLow, $kw)) {
                            $inFooter = true; break;
                        }
                    }
                }

                if ($inFooter) {
                    $fData = [];
                    $fHasData = false;
                    for ($c = 1; $c <= $highestColIdx; $c++) {
                        $val = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
                        $fData[$c] = $val;
                        if ($val !== '') $fHasData = true;
                    }
                    if ($fHasData) $footerRows[] = $fData;
                } else {
                    $rowData = [];
                    $rowHasData = false;
                    for ($c = 1; $c <= $highestColIdx; $c++) {
                        $val = trim((string) $worksheet->getCell([$c, $r])->getFormattedValue());
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

            foreach ($rows as &$row) {
                $jabatanRaw = '';
                foreach ($row as $k => $v) {
                    $up = strtoupper(trim($k));
                    if ($up === 'JABATAN' || $up === 'NAMA' || str_contains($up, 'JABATAN') || str_contains($up, 'NAMA')) {
                        if ((string)$v !== '') {
                            $jabatanRaw .= ' ' . $v;
                        }
                    }
                }
                
                $jabatanRaw = strtolower(trim($jabatanRaw));
                $kode = 0;
                $jabSub = '';
                $isTim = (
                    str_contains($jabatanRaw, 'hakim agung') ||
                    str_contains($jabatanRaw, 'hakim pemilah') ||
                    str_contains($jabatanRaw, 'majelis hakim') ||
                    str_contains($jabatanRaw, 'panmud') ||
                    str_contains($jabatanRaw, 'panitera muda') ||
                    str_contains($jabatanRaw, 'askor') ||
                    str_contains($jabatanRaw, 'staf tim') ||
                    str_contains($jabatanRaw, 'asisten') ||
                    str_contains($jabatanRaw, 'operator') ||
                    str_contains($jabatanRaw, 'tim korektor')
                );

                if ($isTim) {
                    $kode = 1;
                    if (str_contains($jabatanRaw, 'hakim')) $jabSub = 'hakim';
                    elseif (str_contains($jabatanRaw, 'panmud') || str_contains($jabatanRaw, 'panitera muda') || str_contains($jabatanRaw, 'askor')) $jabSub = 'panmud';
                    elseif (str_contains($jabatanRaw, 'asisten')) $jabSub = 'asisten';
                    elseif (str_contains($jabatanRaw, 'operator')) $jabSub = 'operator';
                } else {
                    $isPane = (
                        str_contains($jabatanRaw, 'panitera pengganti') ||
                        str_contains($jabatanRaw, 'juru sita') ||
                        str_contains($jabatanRaw, 'ppk') ||
                        str_contains($jabatanRaw, 'staf penelaah') ||
                        str_contains($jabatanRaw, 'kepaniteraan') ||
                        str_contains($jabatanRaw, 'penanggung jawab')
                    );
                    if ($isPane) {
                        $kode = 2;
                    } elseif ($jabatanRaw !== '') {
                        $kode = 2;
                    }
                }

                $row['_kode_kuitansi'] = $kode;
                $row['_jabatan_sub'] = $jabSub;
            }
            unset($row);

            $finalHeaders = [];
            foreach ($headers as $c => $h) {
                if ($colHasContent[$c]) {
                    $finalHeaders[] = $h;
                }
            }
            
            // Limit to actual data columns to keep it clean (and drop completely blank ones)
            $filteredRows = array_map(function ($row) use ($finalHeaders) {
                $out = [];
                foreach ($finalHeaders as $h) {
                    $out[$h] = $row[$h] ?? '';
                }
                $out['_kode_kuitansi'] = $row['_kode_kuitansi'];
                $out['_jabatan_sub'] = $row['_jabatan_sub'];
                return $out;
            }, $rows);

            $footerBlocks = [];
            if (!empty($footerRows)) {
                $leftLines = [];
                $centerLines = [];
                $rightLines = [];
                
                $midCol = (int) ceil($highestColIdx / 2);
                foreach ($footerRows as $fRow) {
                    $leftStr = ''; $centerStr = ''; $rightStr = '';
                    foreach ($fRow as $c => $val) {
                        if ($c < $midCol - 1) $leftStr .= ' ' . $val;
                        elseif ($c >= $midCol - 1 && $c <= $midCol + 1) $centerStr .= ' ' . $val;
                        else $rightStr .= ' ' . $val;
                    }
                    if (trim($leftStr)) $leftLines[] = trim($leftStr);
                    if (trim($centerStr)) $centerLines[] = trim($centerStr);
                    if (trim($rightStr)) $rightLines[] = trim($rightStr);
                }
                
                if (!empty($leftLines)) $footerBlocks[] = ['position' => 'left', 'lines' => $leftLines];
                if (!empty($centerLines)) $footerBlocks[] = ['position' => 'center', 'lines' => $centerLines];
                if (!empty($rightLines)) $footerBlocks[] = ['position' => 'right', 'lines' => $rightLines];
            }

            $tables[] = [
                'sheetName' => $sheetName,
                'title'     => implode("\n", $titleLines),
                'headers'   => array_values($finalHeaders),
                'rows'      => $filteredRows,
                'footerBlocks' => $footerBlocks,
            ];

            $startBoundary = $endBoundary + 1;
        }

        return $tables;
    }

EOD;

$newContent = substr($content, 0, $startPos) . $newFunction . substr($content, $endPos);
file_put_contents($file, $newContent);
echo "Successfully replaced parseHonorariumSheet.\n";

$content = file_get_contents($file);
$honorariumStart = strpos($content, '    public function honorarium(Request $request)');
$honorariumEnd = strpos($content, '    public function printHonorarium(Request $request)');
if ($honorariumStart !== false && $honorariumEnd !== false) {
    $honorariumFunc = substr($content, $honorariumStart, $honorariumEnd - $honorariumStart);
    $honorariumFunc = str_replace(
        '$parsed = $this->parseHonorariumSheet($ws, $sheetName);
                if ($parsed !== null) {
                    $sheets[] = $parsed;
                }',
        '$parsedTables = $this->parseHonorariumSheet($ws, $sheetName);
                if (!empty($parsedTables)) {
                    foreach ($parsedTables as $idx => $pt) {
                        if (count($parsedTables) > 1) {
                            $pt["sheetName"] = $sheetName . " (Bagian " . ($idx + 1) . ")";
                        }
                        $sheets[] = $pt;
                    }
                }',
        $honorariumFunc
    );
    $newContent = substr($content, 0, $honorariumStart) . $honorariumFunc . substr($content, $honorariumEnd);
    file_put_contents($file, $newContent);
    echo "Successfully updated honorarium().\n";
} else {
    echo "Could not find honorarium() bounds.\n";
}
