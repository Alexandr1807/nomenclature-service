<?php
namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class ProductsCsvImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithChunkReading,
    ShouldQueue,
    SkipsOnFailure
{
    use SkipsFailures;

    public string $userId;
    public int    $chunkSize = 100;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function model(array $row)
    {
        Log::info('Running model() for row, user='.$this->userId);

        return new \App\Models\Product([
            'id'          => (string) \Illuminate\Support\Str::uuid(),
            'name'        => $row['name'],
            'description' => $row['description']  ?? null,
            'category_id' => $row['category_id'],
            'supplier_id' => $row['supplier_id'],
            'price'       => $row['price'],
            'created_by'  => $this->userId,
            'updated_by'  => $this->userId,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.name'        => ['required','string','max:255'],
            '*.description' => ['nullable','string','max:255'],
            '*.category_id' => ['required','uuid','exists:categories,id'],
            '*.supplier_id' => ['required','uuid','exists:suppliers,id'],
            '*.price'       => ['required','numeric','min:0'],
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $f) {
            Log::warning('Import validation failed', $f->toArray());
        }
    }
}
