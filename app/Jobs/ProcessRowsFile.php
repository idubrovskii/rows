<?php

namespace App\Jobs;

use App\Models\Row as RowModel;
use App\Spreadsheet\ChunkFilter;
use App\Spreadsheet\Progress;
use App\Spreadsheet\RowValidator;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use Generator;
use Illuminate\Support\Arr;
use Throwable;

class ProcessRowsFile implements ShouldQueue
{
    use Queueable;

    const int CHUNK_SIZE = 1000;

    private string $filename;
    private array $errors = [];

    private RowValidator $validator;
    private Progress $progress;

    public function __construct(
        string $filename,
    ) {
        $this->filename = Storage::disk('local')->path($filename);
    }

    public function handle(): void
    {
        $this->validator = new RowValidator();
        $this->progress = new Progress($this->filename);

        try {
            foreach ($this->chunkRows($this->getRows()) as $chunk) {
                RowModel::insert($chunk);
            }
            Storage::disk('local')->delete($this->filename);
        } catch (Throwable $t) {
            $this->errors[0] = [$t->getMessage()];
        }

        $this->writeResults();
    }


    private function chunkRows(Generator $rows): Generator
    {
        $result = [];
        foreach($rows as $row) {
            $result[] = $row;
            if(count($result) >= self::CHUNK_SIZE) {
                yield $result;
                $result = [];
            }
        }
        if ($result) {
            yield $result;
        }
    }


    private function getRows(): Generator
    {
        /** @var BaseReader */
        $reader = IOFactory::createReaderForFile($this->filename);
        $reader->setReadDataOnly(true);

        $totalRows = data_get($reader->listWorksheetInfo($this->filename), '0.totalRows', 0);
        if ($totalRows < 1) {
            return;
        }

        $filter = new ChunkFilter(self::CHUNK_SIZE);
        $reader->setReadFilter($filter);

        for($start = 2; $start <= $totalRows; $start += self::CHUNK_SIZE) {
            $filter->setStart($start);
            $sheet = $reader->load($this->filename)->getActiveSheet();
            yield from $this->filterRows($sheet->getRowIterator($start));
        }
    }

    private function filterRows(iterable $rows): Generator
    {
        /** @var Row */
        foreach($rows as $row) {
            if($row->isEmpty()) {
                continue;
            }
            $this->progress->set($row->getRowIndex());
            $data = $this->toInputArray($row);
            if ($this->validator->isValid($data)) {
                $data['date'] = Carbon::parse($data['date']);
                yield $data;
            } else {
                $this->errors[$row->getRowIndex()] = $this->validator->errors();
            }
        }
    }

    private function toInputArray(Row $row): array
    {
        $data = iterator_to_array($row->getCellIterator(endColumn:'C'));
        return [
            'dev_id' => $data['A']?->getValue(),
            'name'   => $data['B']?->getValue(),
            'date'   => $data['C']?->getValue(),
        ];
    }

    private function writeResults(): void
    {
        Storage::put('result.txt', implode("\n", Arr::map(
            $this->errors,
            fn ($errors, $rowId) => sprintf('%d - %s', $rowId, implode(', ', $errors))
        )));
    }
}
