<?php
namespace Modules\Api\Controllers;
use Modules\Api\Models\Role;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use Modules\Api\Models\WaiterShift;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * Get All POS Users
     * @return \Illuminate\Http\Response
     */
    public function getPOSUsers()
    {
        $users = \App\User::select('users.*')
                          ->join('core_roles', 'users.role_id', '=', 'core_roles.id')
                          ->where('core_roles.origin', 'POS');
        $branch = $this->getCurrentBranch();
        if(!empty($branch)) {
            $users->where('branch_id', $branch);
        }
        return response()->json([
            'status' => 1,
            'users'  => $users->orderBy('id', 'DESC')
                            ->with('role','branch', 'creator')
                            ->withTrashed()
                            ->get()
        ]);
    }

    /**
     * Get All POS Roles
     * @return \Illuminate\Http\Response
     */
    public function roles($id = NULL)
    {
        if(!is_null($id)){
            return response()->json([
                'status' => 1,
                'row'    => Role::findOrFail($id)
            ]);
        }
        return response()->json([
            'status' => 1,
            'roles'  => Role::where('origin', 'POS')
                            ->orderBy('name', 'ASC')
                            ->get()
        ]);
    }

    /**
     * Add POS Role
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function createRole(Request $request)
    {
        if($request->has('id')) {
            $role = Role::findOrFail($request->input('id'));
        }
        if(empty($role)) {
            $role = new Role();
        }
        $permissions = json_decode($request->input('permissions'));
        $role->name        = $request->input('name');
        $role->description = $request->input('description');
        $role->status      = 1;
        $role->origin = 'POS';
        $role->permissions = $permissions;
        $role->save();

        return response()->json([
            'status' => 1,
            'row'   => Role::find($role->id)
        ]);
    }

    /**
     * Delete user role
     * @param int $id
     * @return JsonRespose
     */

     public function destroyRole($id)
     {
        Role::where('id', $id)->delete();
        return response()->json([
            'status'  => 1
        ]);
     }

     /**
     * Search for clients
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $keyword = $request->get('query');
        $users = \App\User::select('users.id', 'users.name', 'users.role_id')
                          ->join('core_roles', 'users.role_id', '=', 'core_roles.id');
                          //->where('core_roles.origin', 'POS');

        if (!empty($role = $request->get('role'))) {
            if ($role == "waiters")
                $users->whereIn('users.id', function($query) {
                    $query->select('waiter_id')
                          ->from('pos_orders')
                          ->whereNull('deleted_at')
                          ->where('resolved', 1);
                })->whereIn('users.id', function($query) {
                    $query->select('waiter_id')
                          ->from('waiters_shift')
                          ->whereDate('work_day', '>=', getSystemDate())
                          ->where('blocked', 0)
                          ->whereNull('end_time');
                })->branch();
            if ($role == "cashiers")
                $users->whereIn('users.id', function($query) {
                    $query->select('cashier_id')
                          ->from('pos_orders')
                          ->whereNull('deleted_at')
                          ->where('resolved', 1);
                });
        }
        if(empty($keyword))
            $result = $users->orderBy('users.name', 'ASC')->with('role')->take(250)->get();
        else
            $result = $users->where('users.name', 'LIKE', '%' . $keyword . '%')
                            ->orderBy('users.name', 'ASC')->get();
        return response()->json($result);
    }

     /**
     * Reset User Password or Pin
     * @param Request $request
     * @return JsonResponse
     */
    public function resetUserPassword(Request $request)
    {
        $password = $request->input('password');
        $user = \App\User::find($request->input('id'));
        if($user->is_front_user == 1) {
            $userPin = md5($password);
            if(!empty($row = \App\User::where('user_pin', $userPin)->first())) {
                return response()->json([
                    'status' => 0,
                    'error'  => 'User with the same PIN exists'
                ], 422);
            }
            $user->user_pin = $userPin;
        } else {
            $user->password = Hash::make($password);
        }
        $user->save();
        return response()->json([
            'status' => 1
        ]);
    }

    /**
     * Get Working waiters
     * @param Request $request
     * @return JsonResponse
     */
    public function getWorkingWaiters(Request $request)
    {
        $rows = WaiterShift::selectRaw('waiters_shift.*, users.name as waiter_name, branches.name as waiter_branch')
                             ->join('users', 'waiters_shift.waiter_id', '=', 'users.id')
                             ->leftJoin('branches', 'users.branch_id', '=', 'branches.id')
                             ->whereDate('work_day', '>=', getSystemDate())
                             ->whereNull('end_time');
                             
        $branch = $this->getCurrentBranch();
        if(!empty($branch)) {
            $rows->where('branches.id', $branch);
        }

        return response()->json([
            'status' => 1,
            'rows'   => $rows->orderBy('users.name', 'ASC')
                                ->paginate($this->recordsLimit())
        ]);
    }

    /**
     * Close waiter's shift
     * @param int $id
     * @return JsonResponse
     */
    public function closeWaiterShift(int $id)
    {
        WaiterShift::where('waiter_id', $id)
                    ->update(['end_time' => date('H:i:s')]);
        return response()->json([
            'status' => 1
        ]);
    }
}