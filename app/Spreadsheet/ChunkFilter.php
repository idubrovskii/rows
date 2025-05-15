<?php

namespace App\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ChunkFilter implements IReadFilter
{
    private int $startRow;
    private int $chunkSize;
    private int $endRow;

    public function __construct(
        int $chunkSize,
        int $startRow = 0,
    ) {
        $this->chunkSize = $chunkSize;
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->startRow && $row < $this->endRow;
    }

    public function setStart(int $row): void
    {
        $this->startRow = $row;
        $this->endRow = $row + $this->chunkSize;
    }

    public function setChunkSize(int $size): void
    {
        $this->chunkSize = $size;
        $this->endRow = $this->startRow + $size;
    }
}
