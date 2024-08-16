<?php
 
namespace App\Events;
 
use Modules\Api\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderDeletedEvent 
{
    use SerializesModels;
 
    /**
     * The order instance.
     *
     * @var \Modules\Api\Models\Order
     * @var array $items
     */
    public $order;
    
    /**
     * Create a new event instance.
     *
     * @param  \Modules\Api\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
      $this->order = $order;
    }
}