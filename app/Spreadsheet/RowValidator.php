<?php

namespace App\Spreadsheet;

use App\Models\Row;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RowValidator
{
    private array $errors = [];
    private array $existingIds = [];
    private const array RULES = [
    ];

    public function __construct()
    {
        $this->existingIds = Row::pluck('dev_id')->all();
    }

    public function isValid(array $data): bool
    {
        $validator = Validator::make($data, [
            'dev_id' => Rule::notIn($this->existingIds),
            'date'   => 'date',
        ]);
        $this->errors = $validator->errors()->toArray();
        if(!$validator->fails()) {
            $this->existingIds[] = $data['dev_id'];
        }
        return !$validator->fails();
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
