<?php
 
namespace App\Events;
 
use Modules\Api\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderCreatedEvent 
{
    use SerializesModels;
 
    /**
     * The order instance.
     *
     * @var \Modules\Api\Models\Order
     * @var array $items
     */
    public $order;
    public $items;
 
    /**
     * Create a new event instance.
     *
     * @param  \Modules\Api\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order, array $items)
    {
        $this->order = $order;
        $this->items = $items;
    }
}