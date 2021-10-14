<?php

namespace App\Console\Commands;

use App\Jobs\SaveCustomersJob;
use App\Jobs\SaveOrdersJob;
use App\Jobs\SaveProductsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class SaveDataToDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It retrieves data from json files and saves them into DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Bus::chain([
            new SaveOrdersJob($this->parser('orders.json')),
            new SaveCustomersJob($this->parser('customers.json')),
            new SaveProductsJob($this->parser('products.json')),
        ])->dispatch();


    }

    /**
     * Execute the console command.
     *
     * @param $file_name
     * @return array
     */
    public function parser($file_name)
    {
        $str = file_get_contents(storage_path('app/sample-files/' . $file_name));
        $data = json_decode($str, true);

        return $data;
    }
}
