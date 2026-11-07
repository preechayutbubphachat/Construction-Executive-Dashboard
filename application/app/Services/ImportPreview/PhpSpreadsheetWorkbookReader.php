<?php

namespace App\Services\ImportPreview;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use ZipArchive;

final class PhpSpreadsheetWorkbookReader implements WorkbookReaderInterface
{
    private float $startedAt;

    public function __construct(private readonly float $timeoutSeconds = 30.0, private readonly ?\Closure $clock = null) {}

    public function read(string $path): array
    {
        $this->startedAt = $this->now();
        $this->check($path);
        $size = filesize($path);
        if ($size === false || $size > 10 * 1024 * 1024) {
            throw new RuntimeException('UPLOAD_SIZE_LIMIT');
        }
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'xlsx') {
            throw new RuntimeException('UNSUPPORTED_FORMAT');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if ($mime !== 'application/zip' && $mime !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            throw new RuntimeException('INVALID_XLSX_SIGNATURE');
        }
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('CORRUPT_WORKBOOK');
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (str_contains($name, 'vbaProject.bin') || str_starts_with($name, 'xl/externalLinks/')) {
                $zip->close();
                throw new RuntimeException(str_contains($name, 'vbaProject') ? 'MACRO_REJECTED' : 'EXTERNAL_LINK_REJECTED');
            }
        }
        $zip->close();
        $this->check($path);
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $workbook = $reader->load($path);
        } catch (\Throwable $e) {
            throw new RuntimeException('CORRUPT_WORKBOOK', 0, $e);
        }
        $sheets = $workbook->getSheetNames();
        if (count($sheets) > 10) {
            throw new RuntimeException('SHEET_LIMIT');
        }
        $result = [];
        foreach ($workbook->getWorksheetIterator() as $sheet) {
            $this->check($path);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $columns = $highestColumn === 'A' && $sheet->getCell('A1')->getValue() === null ? 0 : Coordinate::columnIndexFromString($highestColumn);
            if ($highestRow > 5000) {
                throw new RuntimeException('ROW_LIMIT');
            }
            if ($columns > 64) {
                throw new RuntimeException('COLUMN_LIMIT');
            }
            $headers = [];
            for ($c = 1; $c <= $columns; $c++) {
                $headers[] = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($c).'1')->getValue());
            }
            $rows = [];
            for ($r = 2; $r <= $highestRow; $r++) {
                $row = [];
                for ($c = 1; $c <= $columns; $c++) {
                    $row[] = $sheet->getCell(Coordinate::stringFromColumnIndex($c).$r)->getCalculatedValue();
                }
                if (count(array_filter($row, static fn ($v) => $v !== null && $v !== '')) > 0) {
                    $rows[] = $row;
                }
                $this->check($path);
            }
            $result[$sheet->getTitle()] = ['headers' => $headers, 'rows' => $rows];
        }
        $this->check($path);

        return $result;
    }

    private function check(string $path): void
    {
        if ($this->now() - $this->startedAt > $this->timeoutSeconds) {
            throw new RuntimeException('PARSER_TIMEOUT');
        }
    }

    private function now(): float
    {
        return $this->clock ? ($this->clock)() : microtime(true);
    }
}
