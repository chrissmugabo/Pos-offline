<?php
namespace Modules\Api\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Api\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TablesController extends Controller
{
    /**
     * Get All Tables
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json([
            'status' => 1,
            'rows'   => Table::orderBy('id', 'DESC')
                                ->with('branch', 'creator')
                                ->branch()
                                ->paginate($this->recordsLimit())
        ]);
    }

    /**
     * Store or edit table information
     * 
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!empty($id = $request->input('id'))){
            $table = Table::find($id);
        }
        else{
            $table = new Table();
            $table->code = generateOrderCode();
        }
        $table->fill($request->input());
       // $table->active = (int)$request->input('active');
        $table->active = 1;
        $table->save();
        Cache::forget('tables');
        return response()->json([
            'status' => 1,
            'table'  => Table::where('id', $table->id)->with('branch', 'creator')->first()
        ]);
    }

    /**
     * Delete table from database
     * @param int $id
     * @return Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $table = Table::findOrFail($id);
        if(!empty($table)) {
            $table->delete();
        }
        Cache::forget('tables');
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * DShow specific tables
     * @param int $id
     * @return Illuminate\Http\Response
     */
    public function show($id = null)
    {
        if(!is_null($id)) {
            return response()->json([
                'status' => 1,
                'table'  => Table::where('id', $id)->with('branch', 'creator')->first()
            ]);
        }

        $tables = Cache::rememberForever('tables', function() {
            return Table::select('id', 'name', 'capacity')->branch()
                         ->orderBy('name', 'ASC')
                         ->branch()
                         ->get();
        });

        return response()->json([
            'status' => 1,
            'tables'  => $tables
        ]);
    }

    /**
     * Get all locked or active tables
     * @return \Illuninate\Http\JsonResponse
     */
    public function getLockedTables()
    {
        $branch = \request()->get('current_branch') ?? \request()->get('branch_id') ?? auth()->user()->branch_id;
        $result = DB::table('pos_orders')
                    // ->selectRaw("GROUP_CONCAT(`table_id`) AS tables")
                    ->where('status', 'PENDING')
                    //->where('resolved', 0)
                    ->whereNull('deleted_at')
                    ->whereDate('pos_orders.created_at', date('Y-m-d'));
        if($branch) {
            $result->where('branch_id', $branch);
        }
        return response()->json($result->pluck('table_id'));
    }
}