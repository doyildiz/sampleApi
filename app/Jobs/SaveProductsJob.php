<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SaveProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            //Saves products to database
            foreach ($this->data as $datum) {
                Product::updateOrCreate(['id' => $datum['id']], [
                    'id' => $datum['id'],
                    'name' => $datum['name'],
                    'category' => $datum['category'],
                    'price' => (float)$datum['price'],
                    'stock' => $datum['stock'],
                ]);
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
