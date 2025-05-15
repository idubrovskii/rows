<?php

namespace App\Spreadsheet;

use App\Models\Row;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RowValidator
{
    private array $errors = [];
    private array $existingIds = [];

    public function __construct()
    {
        $this->existingIds = Row::pluck('dev_id')->all();
    }

    public function isValid(array $data): bool
    {
        return tap($this->quickCheck($data), function ($result) use ($data) {
            if($result) {
                $this->existingIds[] = $data['dev_id'];
            }
        });
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function quickCheck(array $data): bool
    {
        $errors = [];

        if(!preg_match('/^\d+$/', $data['dev_id']) || in_array($data['dev_id'], $this->existingIds)) {
            $errors[] = 'Wrong id format';
        }

        if(!preg_match('/^[a-zA-Z\s]+$/', $data['name'])) {
            $errors[] = 'Wrong name format';
        }

        try {
            if(!preg_match('/^\d{2}.\d{2}.\d{4}$/', $data['date'])) {
                throw new Exception('wrong format');
            }
            Carbon::parse($data['date']);
        } catch (Exception $e) {
            $errors[] = 'Invalid date: ' . $e->getMessage();
        }

        $this->errors = $errors;
        return !$errors;
    }

    private function properCheck(array $data): bool
    {
        $validator = Validator::make($data, [
            'dev_id' => ['integer', 'gt:0',Rule::notIn($this->existingIds)],
            'date'   => Rule::date()->format('d.m.Y'),
            'name'   => 'regex:/[a-zA-Z\s]+/'
        ]);

        $this->errors = $validator->errors()->toArray();
        if(!$validator->fails()) {
            $this->existingIds[] = $data['dev_id'];
        }
        return !$validator->fails();
    }
}
