<?php

namespace App\Services\ImportPreview;

interface WorkbookReaderInterface
{
    /** @return array<string, array{headers: list<string>, rows: list<list<mixed>>}> */
    public function read(string $path): array;
}
