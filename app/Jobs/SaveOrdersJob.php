<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SaveOrdersJob implements ShouldQueue
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
            //Saves order and details to database
            foreach ($this->data as $datum) {
                $save = Order::updateOrCreate(['id' => $datum['id']], [
                    'id' => $datum['id'],
                    'customerId' => $datum['customerId'],
                    'total' => (float)$datum['total'],
                ]);
                if ($save) {
                    foreach ($datum['items'] as $item) {
                        OrderDetail::updateOrCreate([
                            'orderId' => $datum['id'],
                            'productId' => $item['productId'],
                            'quantity' => $item['quantity'],
                            'unitPrice' => (float)$item['unitPrice'],
                            'total' => (float)$item['total'],
                        ]);
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
