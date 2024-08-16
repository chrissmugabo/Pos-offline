<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Api\Models\Order;
use Modules\Api\Models\Round;
use Modules\Api\Models\OrderItem;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class TestController extends Controller
{
    public function generateInvoice()
    {
        $round = Round::where('order_id', 460)->first();
        $items = OrderItem::select('pos_order_items.*', 'products.name')
                            ->leftJoin('products', 'pos_order_items.item_id', '=', 'products.id')
                            ->where('pos_order_items.order_id', $round->order_id);
        if($round->category == 'ORDER') {
            $items->where('pos_order_items.destination', $round->destination)
                  ->where('pos_order_items.round_key', $round->round_no);
        }
        $items = $items->get();
        $order = Order::select('id', 'reference', 'category', 'system_date', 'order_time', 'table_id', 'waiter_id', 'grand_total', 'status', 'printed', 'client_id', 'branch_id', 'receipts', 'paid', 'resolved')
                        ->where('id', $round->order_id)
                        ->with('table', 'waiter', 'client')
                        ->first();
        //dd(sizeof($items));
        $htmlContent = '
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .h5,.h6{margin-top:0;margin-bottom:.1rem;font-weight:500;}.h5{font-size:1.325rem}.h6,span{font-size:1.25rem}b{font-weight:700}.mb-0{margin-bottom:0!important}.mb-1{margin-bottom:0.25rem!important}.px-1{padding-right:0.25rem!important;padding-left:0.25rem!important}.py-1{padding-top:0.25rem!important;padding-bottom:0.25rem!important}.fs-5{font-size:1.125rem!important}.fw-bolder{font-weight:bolder!important}.text-end{text-align:right!important}.text-center{text-align:center!important}.text-nowrap{white-space:nowrap!important}.border-dashed{border-bottom-style:dashed!important}.my-1{margin-top:0.25rem!important;margin-bottom:0.25rem!important}.me-3{margin-right:1rem!important}.mb-0{margin-bottom:0!important}.mb-2{margin-bottom:0.5rem!important}.ms-auto{margin-left:auto!important}.p-2{padding:0.5rem!important}.px-3{padding-right:1rem!important;padding-left:1rem!important}.py-0{padding-top:0!important;padding-bottom:0!important}.py-2{padding-top:0.5rem!important;padding-bottom:0.5rem!important}.py-4{padding-top:1.5rem!important;padding-bottom:1.5rem!important}.ps-1{padding-left:0.25rem!important}.fw-normal{font-weight:400!important}.fw-bold{font-weight:700!important}.fw-bolder{font-weight:bolder!important}.text-dark{color:#000!important}.d-block{display:block!important}
                    .invoice-box { width: 100%; margin: 0; padding: 10px; }
                    .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
                    .invoice-box table td { padding: 5px; vertical-align: top; }
                    .invoice-box table tr td:nth-child(2) { text-align: right; }
                    .invoice-box table tr.top table td { padding-bottom: 20px; }
                    .invoice-box table tr.top table td.title { font-size: 20px; line-height: 20px; color: #333; }
                    .invoice-box table tr.information table td { padding-bottom: 20px; }
                    .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
                    .invoice-box table tr.details td { padding-bottom: 10px; }
                    .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
                    .invoice-box table tr.item.last td { border-bottom: none; }
                    .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="invoice-box">
                    <table cellpadding="0" cellspacing="0">
                        <tr class="top">
                            <td colspan="3">
                                <div class="fs-5 text-center py-1 border-bottom border-dashed">
                                    <p class="h5 mb-1"><b>'. setting_item("site_name") .'</b></p>
                                    <p class="mb-0 h6">TIN: '. setting_item("app_tin").'</p>
                                    <p class="mb-0 h6">Tel: '. setting_item("app_phone") .'</p>
                                    <p class="mb-0 h6">Email:'. setting_item("app_email").'</p>
                                    <p class="mb-0 h6">Address: '. setting_item("site_address").'</p>
                                </div>
                            </td>
                        </tr>

                          <tr class="information">
                            <td colspan="3">
                                <table class="mb-1">
                                    <tr>
                                        <td colspan="2">
                                            <p class="mb-0 h6">
                                            Order NO:
                                            <b>#' . pad_number($round->round_no) .'</b>
                                            &rarr;
                                            ' . $round->destination . '
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <p class="mb-0 h6">
                                            Customer:
                                            <b>' . "Walk-In" . '</b>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="mb-0 h6">Served By:<b>' . $order->waiter->name . '</b></p>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <p class="mb-0 h6">
                                            Table No: <b>' . $order->table->name .'</b>
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                    <td>
                                        <p class="mb-0 h6">
                                        Date: <b>'. date("Y-m-d", strtotime($order->order_date)) .'</b>
                                        </p>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <p class="mb-0 h6">
                                        <b>'. date("H:i", strtotime($order->order_date)) .'</b>
                                        </p>
                                    </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        
                        <tr class="heading">
                            <td>Item</td>
                            <td> Qty</td>
                            <td class="text-end">Sub.Total</td>
                        </tr>
                         '. implode("", array_map(function($item) {
                                return "<tr  class='item'><td><span class='fw-bold d-block h6'>{$item['name']}</span></td><td><span class='ms-auto fw-bolder text-dark h6 mb-0'>{$item['quantity']}</span></td><td class='text-end'><span class='ms-auto fw-bolder text-dark h6 mb-0'>" . number_format(($item['price']) * $item['quantity']) ."</span></td></tr>";
                            }, $items->toArray())) .' 
                        <tr class="total">
                            <td colspan="2"><span class="fw-bolder h6 mb-0">Grand Total</span></td>
                            <td>
                               <span class="h6 mb-0 fw-bold">'. format_money($order->grand_total) .'</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </body>
            </html>
        ';

        $pdf = PDF::loadHTML($htmlContent)->setPaper('a6');
                 // ->setPaper('portrait', [88, 297]); // Set paper size to 88mm width and auto height
        //$pdf->setPaper('custom', [88, 210])->setOrientation('landscape'); 
        return $pdf->download($order->id . '.pdf');
    }
}
