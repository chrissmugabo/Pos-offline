<?php
 
namespace App\Events;
 
use Modules\Api\Models\Requisition;
use Illuminate\Queue\SerializesModels;

class RequisitionUpdatedEvent 
{
    use SerializesModels;
 
    /**
     * The requisition instance.
     *
     * @var \Modules\Api\Models\Requisition
     */
    public $requisition;
    public $status;
 
    /**
     * Create a new event instance.
     *
     * @param  \Modules\Api\Models\Requisition  $requisition
     * @param string $status
     * @return void
     */
    public function __construct(Requisition $requisition, string $status)
    {
        $this->requisition = $requisition;
        $this->status = $status;
    }
}