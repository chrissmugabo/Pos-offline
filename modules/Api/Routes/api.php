<?php
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Jobs\EnvironmentsMerger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

Route::group(['namespace' => 'App\Http\Controllers', 'middleware' => 'api'], function () {

  Route::get('preloaders', function(Request $request) {
    $rows = Cache::rememberForever('core_settings', function () {
      return Setting::all()->pluck('value', 'key');
    });
    return response()->json((object)$rows);
  });

  Route::post('merge-db', function(Request $request) {
    try {
      $rows = $request->input('rows');
      //Log::info('POST INPUT', $rows);
      dispatch(new EnvironmentsMerger($rows));
      return response()->json(['status' => 1]);
    } catch (\Throwable $th) {
      //throw $th;
     Log::error($th);
      return response()->json(['status' => 0]);
    }
    
  });

  Route::get('run-db-sync', function(Request $request) {
    EnvironmentsMerger::dispatch();
    return response()->json(['status' => 1]);
  })->middleware(['throttle:db-sync-interval']);


});

Route::group(['namespace' => 'Modules\Api\Controllers', 'middleware' => 'api', 'prefix' => 'pos'], function () {

    Route::get('dates-range', function() {
        return response()->json([
            'TODAY'     => [getSystemDate(), getSystemDate()],
            'YESTERDAY' => [date('Y-m-d', strtotime(getSystemDate()) - 86400), getSystemDate()],
            'THIS_WEEK' => getThisWeekBoundaries(),
            'LAST_WEEK' => getLastWeekBoundaries(),
            'PREVIOUS_MONTH' => getMonthBoundaries('first day of previous month', 'last day of previous month'),
            'CURRENT_MONTH'  => getMonthBoundaries('first day of this month', 'last day of this month'),
            'THIS_YEAR'      => getMonthBoundaries('first day of January', 'last day of December'),
            'YEAR_TO_DATE'   => getMonthBoundaries('first day of January', 'today'),
            'LAST_YEAR'      => getMonthBoundaries('first day of January', 'last day of December', '-1 year')
        ]);
    });
    
    /* Register - Login */
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', 'AuthController@login')->middleware(['throttle:60,1']);
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::get('me', 'AuthController@me');
        Route::get('myself', 'AuthController@myself');
        Route::get('profile', 'AuthController@profile');
        Route::get('logins', 'AuthController@logins');
        Route::get('notifications/read', 'AuthController@readNotifications');
        Route::post('me', 'AuthController@updateUser');
        Route::post('authenticate', 'AuthController@authenticate');
        Route::post('change-password', 'AuthController@changePassword');
        Route::get('printing/{code}', 'AuthController@printSettings');
        Route::get('start-shift', 'AuthController@startShift');
        Route::get('close-shift', 'AuthController@closeShift');
    });

    /**
     * Routes for stock items
     */
    Route::group(['prefix' => 'items', 'middleware' => 'auth:api'], function () {
        Route::get('', 'BranchesController@items');
        Route::get('list', 'ItemsController@index');
        Route::get('add-ons', 'ItemsController@getAddons');
        Route::get('types', 'ItemsController@types');
        Route::get('types/destroy/{id}', 'ItemsController@destroyCategory');
        Route::post('types/store', 'ItemsController@storeCategory');
        Route::get('extras', 'ItemsController@extras');
        Route::get('delete/{id}', 'ItemsController@delete');
        Route::post('configure', 'ItemsController@configure');
        Route::post('store', 'ItemsController@store');
        Route::get('configurations/{productId}', 'ItemsController@configurations');
        Route::get('ingredients/delete/{product}/{ingredient}', 'ItemsController@deleteIngredient');
        Route::get('show/{code}', 'ItemsController@singleItem');
        Route::get('ingredients', 'BranchesController@ItemsIngredients');
        Route::get('configurations/{productId}', 'ItemsController@configurations');
        Route::get('search', 'ItemsController@search');
    });

    /* Branches */
    Route::group(['prefix' => 'branches', 'middleware' => 'auth:api'], function () {
        Route::get('', 'BranchesController@index');
        Route::get('show', 'BranchesController@show');
        Route::get('search', 'BranchesController@search');
        Route::post('create', 'BranchesController@store');
        Route::post('update', 'BranchesController@update');
        Route::post('printing/setup', 'BranchesController@setPrintingData');
        Route::get('delete/{id}', 'BranchesController@delete');
        /* Items */
        Route::get('items', 'BranchesController@items');
        Route::get('items/recipes-costing-report', 'BranchesController@recipesCosting');
        Route::get('items/auto-requisitions', 'BranchesController@autoRequisitions');
        Route::post('item/configure', 'ProductsController@configure');
        Route::get('items/track', 'BranchesController@trackItems');
    });
    
    /**
     * For settings
     */
    Route::group(['prefix' => 'settings', 'middleware' => 'auth:api'], function () {
        Route::get('users/permissions/{id?}', 'PermissionsController@index');
        Route::get('users/permissions/delete/{id}', 'PermissionsController@delete');
        Route::post('users/permissions/store', 'PermissionsController@store');
        Route::post('close-day', 'SettingsController@closeWorkingDay');
        Route::get('eod/latest', 'SettingsController@getLastEOD');
    });

    /**
     * For tables controller
     */
    Route::group(['prefix' => 'tables', 'middleware' => 'auth:api'], function () {
        Route::get('', 'TablesController@index');
        Route::get('delete/{id}', 'TablesController@destroy');
        Route::post('store', 'TablesController@store');
        Route::get('show', 'TablesController@show');
        Route::get('locked', 'TablesController@getLockedTables');
    });

     /**
     * For Orders controller
     */
    Route::group(['prefix' => 'orders', 'middleware' => 'auth:api'], function () {
        Route::get('', 'OrdersController@index');
        Route::get('sales', 'OrdersController@sales');
        Route::get('invoices/{orderId}/destroy/{itemId}', 'OrdersController@deleteItem');
        Route::post('receipts/{orderId}/split', 'OrdersController@splitOrderItems');
        Route::post('delete', 'OrdersController@destroy');
        Route::post('destroy', 'OrdersController@destroy');
        Route::post('store', 'OrdersController@store');
        Route::post('payment', 'OrdersController@handlePayment');
        Route::post('deliver', 'OrdersController@deliverOrder');
        Route::get('deliver/{orderId}/{itemId}', 'OrdersController@deliverItem');
        Route::get('delete-item/{itemId}', 'OrdersController@deleteOrderItem');
        Route::get('show', 'OrdersController@show');
        Route::get('show-destination-orders', 'OrdersController@showDestinationOrders');
        Route::get('show-waiter-orders', 'OrdersController@showWaiterOrders');
        Route::get('items/{reference}', 'OrdersController@showOrderItems');
        Route::get('items-by-id/{orderId}', 'OrdersController@showOrderItemsById');
        Route::get('invoice/{id}/print', 'OrdersController@handleOrderPrint');
        Route::get('unpaid', 'OrdersController@getUnpaidOrders');
        Route::post('unpaid/assign', 'OrdersController@assignClient');
        Route::get('waiters/{waiterId}/assigned', 'OrdersController@getWaiterAssignedOrders');
        Route::post('store-printable', 'OrdersController@storePrintableOrder');
    });

    Route::get('next-printable-round', 'OrdersController@getNextOrderToPrint');
    Route::get('update-printed-round/{id}', 'OrdersController@updatePrintedRound');
    Route::post('handle-micro-invester-sales', 'OrdersController@importMicroInvestorSales');
    Route::get('test-connection', function() {
        return isConnected();
    });

     /** 
     * For User controller
     */
    Route::group(['prefix' => 'users', 'middleware' => 'auth:api'], function () {
        Route::get('', 'UsersController@getPOSUsers');
        Route::post('change-user-password', 'UsersController@resetUserPassword');
        Route::get('delete/{id}', 'UsersController@delete');
        Route::post('store', 'UsersController@store');
        Route::get('search', 'UsersController@search');
        Route::get('roles', 'UsersController@roles');
        Route::get('roles/show/{id}', 'UsersController@roles');
        Route::post('roles/store', 'UsersController@createRole');
        Route::get('roles/destroy/{id}', 'UsersController@destroyRole');
        Route::get('active-waiters', 'UsersController@getWorkingWaiters');
        Route::get('close-shift/{id}', 'UsersController@closeWaiterShift');
    });

     /**
     * For Client controller
     */
    Route::group(['prefix' => 'clients', 'middleware' => 'auth:api'], function () {
        Route::get('', 'ClientsController@index');
        Route::get('search', 'ClientsController@search');
        Route::get('balances', 'ClientsController@balances');
        Route::post('create', 'ClientsController@store');
        Route::post('update', 'ClientsController@update');
        Route::get('delete/{id}', 'ClientsController@delete');
    });

    /**
     * For Payments
     */
    Route::group(['prefix' => 'payments', 'middleware' => 'auth:api'], function () {
            Route::get('vounchers/{vounchers}', 'OrdersController@getVouchers');
            Route::post('bulk', 'OrdersController@handleBulkPayment');
            Route::get('delete/{ids}', 'OrdersController@deletePayments');
    });

    Route::post('frontend/preloaders', 'SettingsController@getAppSettings');
    Route::post('preloaders/update', 'SettingsController@setPreloaderItem');
    Route::get('pos-branches', 'BranchesController@show');
    /**
     * For Dashboard controller
     */
    Route::group(['prefix' => 'dashboards', 'middleware' => 'auth:api'], function () {
        Route::get('', 'DashboardController@index');
    });

    /**
     * For Reports controller
     */
    Route::group(['prefix' => 'reports', 'middleware' => 'auth:api'], function () {
        Route::get('sales', 'ReportsController@getSalesReport');
        Route::get('cancellations', 'ReportsController@getCancellationsReport');
        Route::get('orders', 'ReportsController@getOrdersReport');
        Route::get('waiters-balance', 'ReportsController@getWaitersBalance');
        Route::get('waiters-performance', 'ReportsController@getWaitersPerformanceReport');
        Route::get('cashiers', 'ReportsController@getCashierReport');
        Route::get('sales-details', 'ReportsController@getDetailsReport');
        Route::get('sales/payments-received', 'ReportsController@paymentsReceived');
    });
    Route::get('sales/track-paid-after', 'ReportsController@trackPaidAfter');   

    Route::group(['prefix' => 'shared', 'middleware' => 'auth:api'], function () {
        Route::get('payments-meta', 'SharedController@getPaymentsMeta');
        Route::get('occupied-rooms', 'SharedController@getOccupiedRooms');
        Route::get('used-payment-modes', 'SharedController@getUsedPaymentMethods');
    });
});