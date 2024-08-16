<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Api\Models\ProductionTracker;
use Modules\Api\Models\ProductStatus;
use Modules\Api\Models\Stock;

class RunDbStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Structuring DB';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Handling DB-restructure...');
        /* $keys = ['site_address', 'site_name', 'currency', 'site_logo', 'contact_one', 'app_phone', 'app_email', 'app_tin', 'momo_code', 'airtel_code', 'disabled_direct_print'];
        foreach($keys as $key) {
            DB::table('core_settings')->insert([
                'key'   => $key,
                'value' => env(strtoupper($key), '')
            ]);
        } */

        // update adjustment
        /* $rows = DB::table('adjusted_items')->where('committed_date', '2024-08-01')->whereNull('deleted_at')->get();
        foreach($rows as $row) {
            if(!$row->branch && !$row->source) {
                DB::table('product_statuses')
                    ->where('system_date', '2024-08-01')
                    ->whereNull('source_id')
                    ->whereNull('branch_id')
                    ->where('product_id', $row->product_id)
                    ->update(['quantity' => $row->quantity]);
            } elseif($row->branch) {
                DB::table('product_statuses')
                    ->where('system_date', '2024-08-01')
                    ->whereNull('source_id')
                    ->where('branch_id', $row->branch)
                    ->where('product_id', $row->product_id)
                    ->update(['quantity' => $row->quantity]);
            } elseif($row->source) {
                DB::table('product_statuses')
                    ->where('system_date', '2024-08-01')
                    ->where('source_id', $row->source)
                    ->whereNull('branch_id')
                    ->where('product_id', $row->product_id)
                    ->update(['quantity' => $row->quantity]);
            }
        } */

        // Reset quantities
       /* $rows = DB::table('adjusted_items')
                        ->where('committed_date', '2024-08-01')
                        ->whereNotNull('source')
                        ->whereNull('deleted_at')->get();
        foreach($rows as $row) {
            DB::table('stock')->where('source_id', $row->source)
                              ->where('product_id', $row->product_id)
                              ->update(['quantity' => $row->quantity]);

            DB::table('product_statuses')->where('source_id', $row->source)
                              ->where('product_id', $row->product_id)
                              ->update(['quantity' => $row->quantity]);
        }
        $products = DB::table('stock')
                                ->select('product_id', 'stock.source_id')
                                ->whereNull('stock.branch_id')
                                ->get();

        foreach(['2024-08-01', '2024-08-02', '2024-08-03'] as $date) {
            foreach($products as $product) {
                $row = new ProductionTracker(['id' => $product->product_id, 'from' => $date]);
                $received   = $row->getReceiveQtyAttribute();
                $transfered = $row->getTransferedQtyAttribute();
                $spoiled    = $row->getSpoiledQtyAttribute();
                $sold       = $row->getSoldQtyAttribute();
                $qty = $received - ($transfered + $spoiled + $sold);
                $stock = Stock::where('source_id', $product->source_id)
                                ->where('product_id', $product->product_id)
                                ->first();
                $stock->quantity += $qty;
                $stock->save();

                adjustTracker([
                    'product_id' => $product->product_id,
                    'source_id'  => $product->source_id,
                    'system_date' => $date,
                    'quantity' => $qty,
                    'operation' => '+'
                ]);
            }
        } */

        $this->info('Command successfully!');
        return Command::SUCCESS;
    }
}
