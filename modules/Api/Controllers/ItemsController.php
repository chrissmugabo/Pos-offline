<?php
namespace Modules\Api\Controllers;

use Illuminate\Http\Request;
use Modules\Api\Models\ProductType;
use Modules\Api\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class ItemsController extends Controller
{
    /**
     * Get All Branch Items (Menu Items)
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!empty($nopagination = $request->get('nopagination'))) {
            $rows = Cache::rememberForever('items', function() {
                return Item::selectRaw('products.id, products.cost, products.name, products.price, products.type_id, products.quantity, units.name As unit, product_types.name As type, product_types.type As `group`')
                            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                            ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
                            ->where('products.status', 'MENU')
                            ->groupBy('products.id')
                            ->where('product_types.is_addon', 0)
                            ->orderBy('products.name', 'ASC')
                            ->get();
            });
            return response()->json([
                'status' => 1,
                'rows'   => $rows               
            ]);
        }

        $result = Item::selectRaw('products.id, products.cost, products.name, products.price, products.type_id, products.quantity, units.name As unit, product_types.name As type, product_types.type As `group`, COUNT(`configurations`.`product_id`) as ingrendients_count')
                            ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                            ->leftJoin('product_types', 'products.type_id', '=', 'product_types.id')
                            ->leftJoin('configurations', 'products.id', '=', 'configurations.product_id')
                            ->where('products.status', 'MENU');

        if(!empty($type = $request->get('type'))) {
            $result->where('products.type_id', $type);
        }
        return response()->json([
            'status' => 1,
            'rows'   => $result->orderBy('products.name', 'ASC')->paginate($this->recordsLimit())
        ]);
    }

    /**
     * Get Categories of POS menu items
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function types(Request $request)
    {
        $result = ProductType::whereNotNull('type');
        $type = $request->get('type');
        if(!empty($type)){
            $result->where('type', $type);
        }
        return response()->json([
            'status' => 1,
            'rows'   => $result->orderBy('name', 'ASC')->get()
        ]);
    }

    /**
     * Get Units and types of items 
     * Dont confuse this with Addons - These are meta for creating items
     * @return \Illuminate\Http\Response
     */
    public function extras()    
    {
        return $this->sendSuccess([
            'status' => 1,
            'units'  => DB::table('units')->orderBy('id', 'DESC')->get(),
            'types'  => ProductType::where('type', 'LIKE', '%KITCHEN%')
                                    ->orWhere('type', 'LIKE', '%BAR%')
                                    ->orderBy('name', 'ASC')
                                    ->get()
        ]);
    }

    /**
     * Store Item
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->has('id')){
            $item = Item::find($request->input('id'));
        } else {
            $item = new Item();
            $item->code = rand(pow(10, 7), pow(10, 8)-1);
            $item->quantity = $request->input('quantity') ?? 0;
            $item->create_user = $this->currentUser()->id;
            $branch = $this->currentUser()->branch_id;
            if($branch) {
                $item->branch_id = $branch;
            }
        }
        $item->fill($request->input());
        $item->save();
        Cache::forget('items');
        return response()->json([
            'status'  => 1
        ]);
    }

    /**
     * Search for menu Item
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $products = Item::select('id', 'name')->where('status', 'MENU');
        if(empty($keyword))
            $result = $products->orderBy('name', 'ASC')->take(250)->get();
        else
            $result = $products->where('name', 'LIKE', '%' . $keyword . '%')
                            ->orderBy('name', 'ASC')->get();
        return response()->json($result);
    }

    /**
     * Get Add-ons
     * @return JsonResponse
     */
    public function getAddons()
    {
        return response()->json([
            'status' => 1,
            'rows'   => ProductType::select('id', 'name', 'type')
                                    ->where('is_addon', 1)
                                    ->with('items')
                                    ->get()
        ]);
    }

    /**
     * Store Items Type
     * @param Request $request
     * @return JsonResponse
     */
    public function storeCategory(Request $request)    
    {
        if($request->has('id')) {
            $row = ProductType::find($request->input('id'));
        } else {
            $row  = new ProductType();
        }

        $row->name = $request->input('name');
        $row->type = $request->input('type');
        $row->is_addon    = $request->input('is_addon') ?? 0;
        $row->description = $request->input('description');	
        $row->save();
        return response()->json([
            'status'   => 1,
            'category' => $row
        ]);
    }

    /**
     * Delete category Item
     * @param int $id
     * @return JsonResponse
     */
    public function destroyCategory($id)
    {
        $category = ProductType::findOrFail($id);
        if(!$category) {
            return response()->json([
                'status' => 0,
                'error'  => 'Category not found'
            ]);
        }
        $category->delete();
        return response()->json([
            'status' => 1
        ]);
    }
}