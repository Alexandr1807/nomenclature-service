<?php
namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessProductImportBatch implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected array  $rows;
    protected string $userId;

    public function __construct(array $rows, string $userId)
    {
        $this->rows   = $rows;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        Log::info('Import batch start, count='.count($this->rows), ['user'=>$this->userId]);

        foreach ($this->rows as $i => $data) {
            try {
                Product::create([
                    'id'          => (string) Str::uuid(),
                    'name'        => $data['name'],
                    'description' => $data['description'] ?? null,
                    'category_id' => $data['category_id'],
                    'supplier_id' => $data['supplier_id'],
                    'price'       => $data['price'],
                    'created_by'  => $this->userId,
                    'updated_by'  => $this->userId,
                ]);
            } catch (\Throwable $e) {
                Log::error('Import product failed', [
                    'row'   => $i,
                    'error' => $e->getMessage(),
                    'data'  => $data,
                ]);
            }
        }

        Log::info('Imported batch of '.count($this->rows).' products.');
    }
}
