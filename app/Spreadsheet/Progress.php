<?php

namespace App\Spreadsheet;

use Illuminate\Support\Facades\Redis;

class Progress
{
    const PREFIX = 'up';

    private string $key;

    public function __construct(
        string $filename,
    ) {
        $this->key = self::PREFIX . '_' . md5($filename);
    }

    public function set(int $rowIndex): void
    {
        Redis::set($this->key, $rowIndex);
    }
}
