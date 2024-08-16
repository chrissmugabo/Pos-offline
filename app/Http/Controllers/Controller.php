<?php

namespace App\Http\Controllers;

use function Clue\StreamFilter\fun;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function sendError($message,$data = [])
    {

        $data['status'] = 0;

        return $this->sendSuccess($data,$message);

    }

    public function sendSuccess($data = [],$message = '')
    {
        if(is_string($data))
        {
            return response()->json([
                'message'=>$data,
                'status'=>true
            ]);
        }

        if(!isset($data['status'])) $data['status'] = 1;

        if($message)
        $data['message'] = $message;

        return response()->json($data);
    }


    public function currentUser()
    {
        return Auth::user();
    }

    public function recordsLimit()
    {
        if(!empty($perPage = \request()->get('per_page'))) {
            return (int)$perPage;
        }
        return 45;
    }

    public function getCurrentBranch() 
    {
        return  auth()->user()->branch_id ?? \request()->get('current_branch') ?? \request()->get('branch_id');
    }

}
