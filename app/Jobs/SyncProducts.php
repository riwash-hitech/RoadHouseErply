<?php

namespace App\Jobs;

use App\Http\Controllers\Services\ProductService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SyncProducts extends Command
{
    use Dispatchable;

    
    /**
     * Create a new job instance.
     */

    protected $signature = 'syncproduct:cron';
     
    public function __construct()
    {
        //
        // $this->service = $ps;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Log::info("ChronJob Start");
        $service = new ProductService();
        $inputParameters = array(
            "orderBy" => "changed",
            "orderByDir" => "asc",
            "recordsOnPage" => "200",
            // "pageNo" => $this->page,
            "changedSince" => $service->getLastUpdateDate(), 
         ); 

         $service->handleCronJob($inputParameters);
         Log::info("ChronJob END");
    }
}
