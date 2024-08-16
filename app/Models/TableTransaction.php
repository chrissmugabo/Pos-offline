<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableTransaction extends Model
{
    protected $table = "table_transactions";

    protected $appends = ['object'];

    public function getObjectAttribute()
    {
        return $this->object_model::find($this->object_id);
    }

}