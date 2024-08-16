<?php
namespace Modules\Api\Controllers;

use Illuminate\Http\Request;
use App\Packages\XLSXWriter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class SharedController extends Controller
{
    /**
     * Get payment Meta(payment methods and accounts)
     */

     public function getPaymentsMeta()
     {
        $accounts = DB::table('accounts')
                        ->select('id', 'name')
                        ->whereNull('deleted_at');
        $branch = $this->getCurrentBranch();
        if($branch) {
            $accounts = $accounts->whereJsonContains('branches', (int)$branch);
            /*$accounts->orWhere(function($q) use($branch) {
                $q->whereNotNull('branches')->whereRaw('JSON_CONTAINS(branches, ?, "$")', [$branch]);
            }); */
        }
        return response()->json([
            'status'   => 1,
            'accounts' => $accounts->get(),
            'modes'    => DB::table('payment_methods')->select('id', 'name')->whereNull('deleted_at')->get(),
        ]);
     }

    /**
     * Handle Excel Exporting
     * @param \Illuminate\Http\Request
     */
    public function handleExcelExport(Request $request)
    {
        $data     = json_decode($request->input('dataset'));
        $filename = $request->input('filename') . "_" . date('Y-m-d') . '-' . date('H:i') . ".xlsx";
        $headings = json_decode($request->input('columns'));
        #header("Content-Type: application/vnd.ms-excel");
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age:0');
        header('Content-Encoding: UTF-8');
        header('Pragma: no-cache');

        /*
        echo implode("\t", $headings) . "\n";
        foreach($data as $row) {
            echo implode("\t", (array)$row) . "\n";
        } 
        */
        $table = '<table><thead><tr><th>' . implode('</th><th>', $headings) . '</th></tr></thead><tbody>';
        foreach($data as $row) {
            $table .='<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
        }
        $table .= '</table><tbody>';
        echo $table;
  		exit;
    }

    /**
     * Handle Exporting
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Support\Facades\Response;
     */
    public function handleExport(Request $request)
    {
        $data     = json_decode($request->input('dataset'));
        $filename = $request->input('filename') . "_" . date('Y-m-d') . '-' . date('H:i') . ".xlsx";
        $headings = json_decode($request->input('columns'));
        $table = implode("\t", $headings) . "\n";
        foreach($data as $row) {
            $table .= implode("\t", (array)$row) . "\n";
        } 
        return Response::make($table, 200)
                        ->header("Content-Type", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8")
                        ->header("Content-Disposition", "attachment; filename=\"$filename\"")
                        ->header("Cache-Control", "max-age:0")
                        ->header("Content-Encoding", "UTF-8")
                        ->header("Pragma", "no-cache");
                        
    }

    /**
     * Handle Exporting
     * @param \Illuminate\Http\Request
     */
    public function handleXLSXExport(Request $request) : void
    {
        $data     = json_decode($request->input('dataset'));
        $filename = $request->input('filename') . "_" . date('Y-m-d') . '-' . date('H:i') . ".xlsx";
        $headings = json_decode($request->input('columns'));
        header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', TRUE, 200);
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $writer = new XLSXWriter();
        $writer->setAuthor(env('APP_NAME', 'Tame'));
        $writer->writeSheetHeader('Sheet1', $headings );
        foreach($data as $row) {
            $writer->writeSheetRow('Sheet1', (array)$row );
        }
        $writer->writeToStdOut();
        exit(0);
    }

    /**
     * Get payment types which received payments
     */
    public function getUsedPaymentMethods()
    {
        return response()->json([
            'status' => 1,
            'rows'   => DB::table('payments')->selectRaw('DISTINCT(payments.payment_type) as payment_type, payment_methods.name')
                                            ->join('payment_methods', 'payments.payment_type', '=', 'payment_methods.id')
                                            ->get()
        ]);
    }

    public function getOccupiedRooms(Request $request)
    {
        $keyword = $request->get('query');
        $rows =  DB::table('acco_room_bookings')
                        ->selectRaw("acco_room_bookings.room_id as id, CONCAT('Room: ', acco_room_assignments.room_name, ' - ', clients.name, '') AS name, clients.id as client_id")
                        ->join('acco_room_assignments', 'acco_room_bookings.room_id', '=', 'acco_room_assignments.id')
                        ->join('acco_bookings', 'acco_room_bookings.booking_id', '=', 'acco_bookings.id')
                        ->leftJoin('clients', 'acco_bookings.customer_id', '=', 'clients.id')
                        ->where('acco_room_bookings.status', 'OCCUPIED');
         if(empty($keyword))
            $rows = $rows->orderBy('acco_room_assignments.room_name', 'ASC')->take(250);
        else
            $rows = $rows->where('acco_room_assignments.room_name', 'LIKE', '%' . $keyword . '%')
                         ->orderBy('acco_room_assignments.room_name', 'ASC');

        return response()->json($rows->get());
    }
}
