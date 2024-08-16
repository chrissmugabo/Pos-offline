<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TableTransaction;
use Illuminate\Support\Facades\Log;
use App\Services\HttpService;

class EnvironmentsMerger implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transactions;

    public function __construct(array $transactions = []) {
        $this->transactions = $transactions;
    }
    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        if(sizeof($this->transactions)) { // Which means we're online we want to save data from offline
            foreach ($this->transactions as $transaction) {
                $row = (array)$transaction['object'];
                switch ($transaction['operation']) {
                    case 'CREATE':
                        $transaction['object_model']::create($row);
                        break;
                    case 'UPDATE':
                        $transaction['object_model']::update($row, ['id' => $transaction['object_id']]);
                        break;
                    case 'DELETE':
                        $transaction['object_model']::where('id', $transaction['object_id'])->delete();
                        break;
                    default:
                        # code...
                        break;
                }
            }
        } else { // We are pushing data from offline to online
            if(isConnected()) { 
                $http = new HttpService(config('app.sync_url'));
     
                $rows =  TableTransaction::select('id', 'object_id', 'object_model', 'operation')
                                            ->where('synchronized', 0)
                                            ->limit('5')
                                            ->get();
                 try {
                     $result = $http->postRequest('api/merge-db', ['rows' => $rows]);
                     //Log::info($result);
                     $status = json_decode($result)->status;
                     if($status) {
                         $ids = array_column($rows, 'id');
                         DB::table('table_transactions')->whereIn('id', $ids)->update(['synchronized' => 1]);
                     }
                 } catch (\Throwable $th) {
                     //throw $th;
                 }
             }
        }
    }
}