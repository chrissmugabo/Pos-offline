<?php
    use Modules\Core\Models\Settings;
    use Carbon\Carbon;
    use Modules\Api\Models\Account;
    use Modules\Api\Models\Transaction;
    use Modules\Api\Models\Tracker;
    use Modules\Api\Models\EndOfDay;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Modules\Api\Models\ProductTracker;
    use Modules\Api\Models\ProductStatus;
    use Modules\Api\Models\StockTracker;
    use Modules\Api\Models\Client;
    use Illuminate\Support\Facades\Cache;
    use Modules\Api\Models\CustomersBalance;
    use Modules\Api\Models\ProductionTracker;

    define( 'MINUTE_IN_SECONDS', 60 );
    define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
    define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
    define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
    define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
    define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
    define( 'PAYMENT_RECEIVED', 'PAYMENT RECEIVED');
    define( 'SUPPLIER_PAYMENT', 'SUPPLIER PAYMENT');
    define( 'AMOUNT_SENT', 'AMOUNT SENT');
    define( 'AMOUNT_RECEIVED', 'AMOUNT RECEIVED');
    define( 'EXPENSE_PAYMENT', 'EXPENSE PAYMENT');

    function generateReference($length) {
        $reference = implode('', [
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(chr((ord(random_bytes(1)) & 0x0F) | 0x40)) . bin2hex(random_bytes(1)),
            bin2hex(chr((ord(random_bytes(1)) & 0x3F) | 0x80)) . bin2hex(random_bytes(1)),
            bin2hex(random_bytes(2))
        ]);

        return strlen($reference) > $length ? substr($reference, 0, $length) : $reference;
    }

    function generateOrderCode($len = 8){
        return strtoupper(substr(base_convert(uniqid(mt_rand()), 16, 36), 0, $len));
    }

    function getMonthDays($month = NULL, $year = NULL)
    {
        if(is_null($month)) $month = date('m');
        if(is_null($year)) $year = date('Y');
        $startDate = "01-" . $month . "-" . $year;
        $startTime = strtotime($startDate);
        $endTime = strtotime("+1 month", $startTime);
        for($i=$startTime; $i < $endTime; $i+=86400){
            $list[] = date('Y-m-d', $i);
        }
        return $list;
    }

    function getMonthWeeks($yearMonth)
    {
        $month = date('m', strtotime($yearMonth));
        $year = date('Y', strtotime($yearMonth));
        $startDate = "01-" . $month . "-" . $year;
        $startTime = strtotime($startDate);
        $endTime = strtotime("+1 month", $startTime);
        $range = new \stdClass;
        $range->start = date('Y-m-d', strtotime($startDate));
        $range->end = date('Y-m-d', strtotime('next sunday', strtotime($startDate)));
        $n = $range->end;
        $result[] = $range;
        $labels[] = "{$range->start} - {$range->end}";
        $nextSunday = $range->end;
        while(date('Y-m', strtotime($nextSunday)) == $yearMonth){
            $range = new \stdClass;
            $start = date('Y-m-d', strtotime('+1 day', strtotime($nextSunday)));
            $range->start = $start;
            $range->end = date('Y-m-d', strtotime('next sunday', strtotime($start)));
            $result[] = $range;
            $labels[] = "{$range->start} - {$range->end}";
            $nextSunday = $range->end;
        }
        return array("labels" => $labels, "weeks" => $result);
    }


    function getUrlLocation()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if(1 < 2){
            // Default for localhost
            $query = array
                (
                    'country' => 'Rwanda',
                    'countryCode' => 'RW',
                    'region' => '05',
                    'regionName' => 'Kigali City',
                    'city' => 'Nyarugenge',
                    'zip' => '',
                    'lat' => '-2.4592',
                    'lon' => '29.5647',
                    'timezone' => 'Africa/Kigali',
                    'isp' => 'BSC',
                    'org' => '',
                    'as' => 'AS37228 KT RWANDA NETWORK Ltd',
                    'query' => '197.243.112.150'
                );
                return $query;
        }else{
            $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));
            $query['query'];
            if ($query && $query['status'] == 'success') {
                return $query;
            }   
        } 
    }

    /**
     * Increase or decrease Account balance
     * @param int $accountId
     * @param double $amount
     * @return void
     */
    function handleAccountBalance($accountId, $amount, array $meta = []) :void
    {
        $account = Account::find($accountId);
        if (!$account) {
            $account = Account::orderBy('id', 'ASC')->first();
        }
        $previousBalance = $account->total_balance;
        $account->total_balance += $amount;
        $account->save();
        recordAccountTransaction(array_merge($meta, ['account_id' => $accountId, 'amount' => $amount, 'current_balance' => $previousBalance]));
    }

    /**
     * Record a transaction
     */

     function recordAccountTransaction(array $data) : void
     {
        $row = Transaction::where('reference_id', $data['reference_id'])
                            ->where('type', $data['type'])
                            ->where('origin', $data['origin'])
                            ->first();
        if (!$row) {
            $currentBalance = Transaction::select('running_balance')
                                           ->where('account_id', $data['account_id'])
                                           ->orderBy('id', 'DESC')->first();
            if($currentBalance) {
                $currentBalance = $currentBalance->running_balance;
            } else {
                $currentBalance = $data['current_balance'] ?? 0;
            }
            Transaction::create([
                'transaction_date' => $data['transaction_date'],
                'action'           => $data['amount'] < 0 ? 'CREDIT' : 'DEBIT',
                'type'             => $data['type'],
                'origin'           => $data['origin'],
                'reference_id'     => $data['reference_id'],
                'account_id'       => $data['account_id'],
                'amount'           => $data['amount'],
                'previous_balance' => $currentBalance,
                'running_balance'  => $currentBalance + $data['amount'],	
                'description'      => NULL
            ]);
        } else {
            $row->amount += $data['amount'];
            $row->running_balance += $data['amount'];
            $row->save();

            //After update transaction row, find newest transactions and update them too
            $rowsAfter = Transaction::where('account_id', $data['account_id'])
                                      //->where('transaction_date', '>', date('Y-m-d', strtotime($row->transaction_date)))
                                      ->where('id', '>', $row->id)
                                      ->get();
            if($row->amount <= 0) {
                $row->delete();
            }
            foreach($rowsAfter as $row) {
                $row->previous_balance += $data['amount'];
                $row->running_balance += $data['amount'];
                $row->save();
            }
        }
     }

    /**
     * Set key in .env file
     */
    function setEnvironment(array $items)
    {
        $str = file_get_contents(base_path('.env'));

        if (count($items) > 0) {
            foreach ($items as $item) {
                $key = strtoupper($item->key);
                $str .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition = strpos($str, "{$key}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$key}='{$item->value}'\n";
                } else {
                    $str = str_replace($oldLine, "{$key}='{$item->value}'", $str);
                }
            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents(base_path('.env'), $str)) return false;
        return true;
    }


    function getCostPrice($product_id)
    {
        $tariff = 0;
        $ingredients = DB::select("SELECT GROUP_CONCAT(`ingredient`) AS ingredients FROM `configurations` WHERE `product_id` = ? AND `ingredient` IN(SELECT `product_id` FROM `configurations`)", [$product_id]);
        $ingredients = $ingredients[0]->ingredients;
        if($ingredients) {
            $items = explode(",", $ingredients);
            $row = DB::table('products')->selectRaw('SUM(products.cost * configurations.quantity) as total_amount')
                                ->join('configurations', 'products.id', '=', 'configurations.ingredient')
                                ->whereNull('configurations.deleted_at')
                                ->whereIn('configurations.product_id', $items)
                                ->first();
            if(!is_null($row)){
                $data = DB::table('configurations')->selectRaw('SUM(quantity) as total_quantity')->whereIn('ingredient', $items)->first();
                if(!is_null($data))
                    $tariff = $row->total_amount * $data->total_quantity;
            } 
        }
        
        $result = DB::table('configurations')->selectRaw('SUM(products.cost * configurations.quantity) as total_amount')
                                ->leftJoin('products', 'configurations.ingredient', '=', 'products.id')
                                ->where('configurations.product_id', $product_id)
                                ->first();
        if(!is_null($result)) 
            return $result->total_amount + $tariff;
        else return NULL;
    }

    /**
     * Pad Number
     * @param int $number
     * @return int $length
     */
    function pad_number($number, $length = 5)
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Get First and last dates of this week
     * @return array
     */
    function getThisWeekBoundaries()
    {
        // Get the current date
        $currentDate = new DateTime();

        // Set the current date to the first day of the week (Sunday)
        $currentDate->modify('this week');

        // Get the first day of the week (Sunday)
        $firstDayOfWeek = clone $currentDate;

        // Set the current date to the last day of the week (Saturday)
        $currentDate->modify('this week +6 days');

        // Get the last day of the week (Saturday)
        $lastDayOfWeek = clone $currentDate;

        // Format the dates as desired
        $firstDayOfWeekFormatted = $firstDayOfWeek->format('Y-m-d');
        $lastDayOfWeekFormatted = $lastDayOfWeek->format('Y-m-d');
        return [$firstDayOfWeekFormatted, $lastDayOfWeekFormatted];
    }

    /**
     * Get First and last dates of last week
     * @return array
     */
    function getLastWeekBoundaries()
    {
        // Set the current date
        $currentDate = new DateTime();
        // Calculate the previous week's start date
        $previousWeekStart = clone $currentDate;
        $previousWeekStart->modify('-1 week');
        $previousWeekStart->modify('Monday this week');

        // Calculate the previous week's end date
        $previousWeekEnd = clone $previousWeekStart;
        $previousWeekEnd->modify('Sunday this week');

        // Format the dates as desired (e.g., 'Y-m-d')
        $startDate = $previousWeekStart->format('Y-m-d');
        $endDate = $previousWeekEnd->format('Y-m-d');
        return [$startDate, $endDate];
    }

    /**
     * get Previous Month
     */
    function getMonthBoundaries($start, $end, $custom = null)
    {
        // Set the current date
        $currentDate = new DateTime();

        // Calculate the previous month's start date
        $previousMonthStart = clone $currentDate;
        if($custom) {
            $previousMonthStart->modify($custom);
        }
        $previousMonthStart->modify($start);

        // Calculate the previous month's end date
        $previousMonthEnd = clone $currentDate;
        if($custom) {
            $previousMonthEnd->modify($custom);
        }
        $previousMonthEnd->modify($end);

        // Format the dates as desired (e.g., 'Y-m-d')
        $startDate = $previousMonthStart->format('Y-m-d');
        $endDate = $previousMonthEnd->format('Y-m-d');
        return [$startDate, $endDate];
    }

   

    function getDatesBetween($start, $end) {
        $dates = array();
        $current = new DateTime($start);
    
        while ($current <= new DateTime($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }
    
        return $dates;
    }

    /**
     * Upload file
    */
    function storeFile($file)
    {
        $folder = '';
        $id = auth()->id();
        if ($id) {
            $folder .= sprintf('%04d', (int)$id / 1000) . '/' . $id . '/';
        }
        $folder = $folder . date('Y/m/d');
        $newFileName = Str::slug(substr($file->getClientOriginalName(), 0, strrpos($file->getClientOriginalName(), '.')));
        if(empty($newFileName)) $newFileName = md5($file->getClientOriginalName());

        $i = 0;
        do {
            $newFileName2 = $newFileName . ($i ? $i : '');
            $testPath = $folder . '/' . $newFileName2 . '.' . $file->getClientOriginalExtension();
            $i++;
        } while (Storage::disk('uploads')->exists($testPath));

        $check = $file->storeAs( $folder, $newFileName2 . '.' . $file->getClientOriginalExtension(), 'uploads');
        return $check;
    }

    function periodDate($startDate, $endDate, $day = true, $interval = '1 day')
    {
        $begin = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        if($day) {
            $end = $end->modify('+1 day');
        }
        $interval = \DateInterval::createFromDateString($interval);
        $period = new \DatePeriod($begin, $interval, $end);
        return $period;
    }

    function getAllTimeRange()
    {
        return [date('Y-m-d', strtotime('2022-01-01')), date('Y-m-d')];
    }

    function getSystemDate() {
        $branch = auth()->user()->branch_id ?? \request()->get('current_branch') ?? \request()->get('branch_id');
        if(!$branch) {
            return date('Y-m-d');
        }
        return Cache::rememberForever($branch . '_system_date', function () use ($branch) {
            $row =  DB::table('pos_end_of_days')
                         ->select('to_date')
                         ->where('branch_id', $branch)
                         ->orderBy('id', 'DESC')
                         ->first();
            return $row ? date('Y-m-d', strtotime($row->to_date))  : date('Y-m-d');
        });
    }

    function updateCustomerBalance(array $args) {

        // int $clientId, float $balance, string $operation = '+'
        $operation = $args['operation'];
        $client = Client::find($args['client_id']);
        if($client) {
            if($operation == '+') {
                $client->balance += $args['balance'];
            } else {
                $client->balance -= $args['balance'];
            }
            $client->save();
            // Ajust Custom Balance
            $rows = CustomersBalance::where('client_id', $args['client_id'])
                                      ->whereDate('system_date', '>', $args['system_date'])
                                      ->get();
            if(!empty($rows)) {
                if($operation == '+') {
                    foreach ($rows as $row) {
                        $row->balance += $args['balance'];
                        $row->save();
                    }
                } else {
                    foreach ($rows as $row) {
                        $row->balance -= $args['balance'];
                        $row->save();
                    }   
                }
            }
        }
    }

    function setting_item($key, $default = NULL)
    {
        return env(strtoupper($key), $default);
    }

    function format_money($number)
    {
        return setting_item('CURRENCY', 'RWF') . ' ' . number_format($number);
    }

    function isConnected() 
    {
        if(!config('app.sync_url') || config('app.pos_env') === 'ONLINE') {
            return false;
        }

        $host = 'tameaps.com';
        $port = 80; 
        $timeout = 5;

        $connected = @fsockopen($host, $port, $errno, $errstr, $timeout);
    
        if ($connected) {
            fclose($connected);
            return true;
        } else {
            return false;
        }
    }

    function isOffline()
    {
        return config('app.sync_url') && config('app.pos_env') === 'OFFLINE';
    }
    

