<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function details()
    {
        return $this->hasMany('App\Models\OrderDetail', 'orderId');
    }

    /**
     * return void
     * when deleting an order, it also deletes its details
     */
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($order) { // before delete() method call this
            $order->details()->each(function ($order) {
                $order->delete();
            });
        });
    }
}
