<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class RoomMessage extends Model
{
    use HasFactory;

    protected $fillable = ['type','room_id','sender_id','receiver_id','service_coupon','content','service_discount','service_user_id','service_order_id','service_status','service_payment_status','service_payment_type','service_total_price','service_admin_commission_percentage','service_app_percentage','service_name','service_description','service_added_value','service_price','service_admin_commission_value','read_at'];

    public function room(){
        return $this->belongsTo(Room::class);
    }

    public function content(){
        $content = '';
        if($this->type == 'image' ||$this->type == 'file'){
            $content =  url('assets/uploads/chat/'.$this->content);
        }else{
            $content = $this->content;
        }
//        $content = $this->content;
        return $content;
    }

    public function contentMsg(){
        $content = '';
        if($this->type == 'image' ){
            $content = trans('user.image');
        }
        elseif( $this->type == 'file'){
            $content = trans('user.file');
        }elseif( $this->type == 'service'){
            $content = trans('user.service');
        }
        else{
            $content = $this->content;
        }

//        $content = $this->content;
        return $content;
    }

    public function markAsRead()
    {
        $this->update(['read_at' => Carbon::now()]);
    }

}
