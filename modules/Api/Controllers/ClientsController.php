<?php

namespace Modules\Api\Controllers;

use Validator;
use Illuminate\Http\Request;
use Modules\Api\Models\Client;
use Modules\Api\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ClientsController extends Controller
{
    /**
     * Search for clients
     * @param \Illuminate\Http\Request $request
     */
    public function search(Request $request)
    {
        $keyword = $request->get('query');
        $q = 'id, name, phone, tin_number';
        if($request->has('with_phones')) {
            $q = "id, CASE WHEN name IS NOT NULL AND phone IS NOT NULL THEN CONCAT(name, ' <br/> (', phone, ')') ELSE name END AS alias, name, phone, tin_number";
        }
        $result =  Client::selectRaw($q);
        $branch = auth()->user()->branch_id ?? $request->get('current_branch') ?? $request->get('branch_id');
        if(empty($keyword))
            $result = $result->orderBy('name', 'ASC')->take(250);
        else
            $result = $result->where('name', 'LIKE', '%' . $keyword . '%')
                            ->orderBy('name', 'ASC');
        if(!empty($branch)) {
            $result->where('branch_id', $branch);
        }
        return response()->json($result->get());
    }
}