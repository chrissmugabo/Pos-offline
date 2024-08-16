<?php
 
namespace App\Listeners;

use App\Events\SuccessLoginEvent;
use App\Notifications\AdminChannelServices;
use Illuminate\Support\Facades\DB;
use Notification;
use Jenssegers\Agent\Agent;

class SuccessLoginListener {
    /**
     * Create the event listener.
     *
     * @return void
    */
    public function __construct()
    {
        //
    }
 
    /**
     * Handle the event.
     *
     * @param  \App\Events\SuccessLoginEvent  $event
     * @return void
     */
    public function handle(SuccessLoginEvent $event)
    {
        $user = $event->user;
        $user->last_login_at = \Carbon\Carbon::now();
        $user->save();
        $location = getUrlLocation();
        $agent = new Agent();
        DB::table('logins')->insert([
            'user_id'       => $user->id,
            'country'       => $location['country'],
            'country_code'	=> $location['countryCode'],
            'city'	        => $location['city'],
            'ip'	        => $location['query'],
            'isp'	        => $location['isp'],
            'login_time'	=> \Carbon\Carbon::now(),
            'user_agent'	=> implode(" on ", [$agent->browser(), $agent->platform()])
        ]);

        $data = [
            'id' =>  $user->id,
            'event'   => 'NEW_LOGIN',
            'avatar' =>  NULL,
            'link' => '/account/profile?reference=',
            'message' => __(':name has logged in at :time', ['name' => $user->name, 'time' => \Carbon\Carbon::now()])
        ];
        $users = \App\User::whereNull('branch_id')->whereNull('source_id')->get();
        Notification::send($users, new AdminChannelServices($data));   
    }
}