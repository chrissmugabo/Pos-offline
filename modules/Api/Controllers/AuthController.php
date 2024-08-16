<?php

namespace Modules\Api\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\Events\SuccessLoginEvent;
use Illuminate\Support\Facades\Auth;
use Modules\Api\Models\BranchSetting;
use Modules\Api\Models\WaiterShift;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->middleware('auth:api', ['except' => ['']]);
    }
    /**
     * Authenticate waiter for by using PIN
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        $user = \App\User::where('user_pin', md5($request->input('password')))
                                    ->select('id', 'name', 'role_id')
                                    ->with('role')
                                    ->first();
        if(is_null($user)) {
            return response()->json([
                'status' => 0,
                'error'  => 'Invalid PIN. try again'
            ], 401);
        } else {
            $token = JWTAuth::fromUser($user);
            if(config('app.pos_env') === 'ONLINE') {
                event(new SuccessLoginEvent($user));
            }
            return response()->json([
                'status' => 1,
                'user'   => $user,
                'token'  => $token
            ]);
        }
    }
     /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myself(Request $request)
    {
        if(empty(Auth::id())) {
            $header = \request()->header('Authorization');
            $token = explode(" ", $header)[1];
            return response()->json([
                'user' => JWTAuth::setToken($token)->toUser()
            ]);
        } else {
            $user = \App\User::where('id', Auth::id())
                            ->with('role', 'branch', 'source')->first();
            return $this->sendSuccess([
                'user' => $user
            ]);
        }
    }

    /**
     * Get Printing Setting For a Branch
     * @param string $code
     * @return JsonResponse
     */
    public function printSettings($code)
    {
        $row = BranchSetting::select('branch_id', 'content')->where('code', $code)->first();
        if(!$row) {
            return response()->json([
                'status' => 0
            ], 422);
        }
        $latestPrint = DB::table('pos_rounds')
                            ->where('branch_id', $row->branch_id)
                            ->where('printed', 1)
                            ->max('id');
        $row->latest_print = $latestPrint ?? 0;
        return response()->json($row);
    }

    public function startShift(Request $request)
    {
        WaiterShift::create([
            'waiter_id'  => auth()->id(),
            'work_day'   => date('Y-m-d'),	
            'start_time' => date('H:i:s'),	
            'blocked' => 0
        ]);
        return response()->json([
            'status' => 1
        ]);
    }

    public function closeShift()
    {
        WaiterShift::where('waiter_id', auth()->id())
                    ->update(['end_time' => date('H:i:s')]);
        return response()->json([
            'status' => 1
        ]);
    }
}
