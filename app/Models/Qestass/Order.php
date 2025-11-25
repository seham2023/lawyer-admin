<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'store_name',
        'store_icon',
        'place_id',
        'consultant_time',
        'place_name',
        'place_icon',
        'deliver_time',
        'receive_lat',
        'receive_long',
        'receive_address',
        'deliver_lat',
        'deliver_long',
        'deliver_address',
        'coupon',
        'type',
        'payment_type',
        'description',
        'delivery_price',
        'price',
        'admin_commission_value',
        'admin_commission_percentage',
        'added_value',
        'discount',
        'total_price',
        'user_id',
        'min_expected_price',
        'max_expected_price',
        'lawyer_id',
        'payment_status',
        'delivery_status',
        'status',
        'price',
        'total_price',
        'invoice_image',
        'have_invoice',
        'close_reason',
        'store_status',
        'needs_delivery',
        'lawyer_acceptance',
        'gender',
        'connection_type',
        'consultant_id',
        'consultant_date',
        'spetialist_id',
        'offer_id',
        'specialist_type',
        'consultant_period',
        'language',
        'category_id',
        'subcategory_id',
        'report_description',
        'rated',
        'has_report',
        'app_percentage',
        'is_reported',
        'consultant_end',
        'interval_id',

    ];

    public function statusForUser()
    {
        // order_type: enum('special_stores','google_places','parcel_delivery','special_request')
        // order_status: enum('open','inprogress','finished','closed')
        // store_status: enum('pending','accepted','rejected')
        // delivery_status: enum('pending','accepted','delivering','reached_store','reached_receive_location','reached_deliver_location','delivered')
        $status = $this->status;
        if ($this->type == 'immediate_consultants' || $this->type == 'lawyer_request') {
            // delivery status is the order status
            if ($this->delivery_status == 'pending') {
                $status = 'pending';
            } elseif ($this->delivery_status == 'accepted' && $this->payment_status == 'pending' && $this->payment_type != 'tabby') {
                $status = 'waiting_payment';
            } elseif ($this->delivery_status == 'accepted' && $this->payment_status == 'pending' && $this->payment_type == 'tabby') {
                $status = 'waiting_tabby_success';
            } elseif ($this->delivery_status == 'accepted' && $this->status == 'inprogress') {
                $status = 'inprogress';
            }
            // order main status being finished or closed
            if ($this->status == 'finished') {
                $status = 'finished';
            } elseif ($this->status == 'closed') {
                $status = 'canceled';
            }
        }


        if ($this->type == 'appointment_booking') {
            // delivery status is the order status
            if ($this->delivery_status == 'pending' && $this->status == 'pending') {
                $status = 'pending';
            } elseif ($this->status == 'open') {
                $status = 'waiting_payment';
            } elseif ($this->delivery_status == 'pending' && $this->status == 'inprogress') {
                $status = 'inprogress';
            } elseif ($this->delivery_status == 'accepted' && $this->status == 'inprogress') {
                $status = 'inprogress';
            }
            // order main status being finished or closed
            if ($this->status == 'finished') {
                $status = 'finished';
            } elseif ($this->status == 'closed') {
                $status = 'canceled';
            }
        }

        return $status;
    }

    public function statusForLawyer()
    {
        // order_type: enum('special_stores','google_places','parcel_delivery','special_request')
        // order_status: enum('open','inprogress','finished','closed')
        // store_status: enum('pending','accepted','rejected')
        // delivery_status: enum('pending','accepted','delivering','reached_store','reached_receive_location','reached_deliver_location','delivered')
        $status = $this->status;
        if ($this->type == 'special_stores' && $this->needs_delivery == 'true') {
            // delivery status is the order status
            if ($this->delivery_status == 'pending') {
                $status = 'pending';
            } elseif ($this->delivery_status == 'accepted') {
                $status = 'inprogress';
            } elseif ($this->delivery_status == 'delivering') {
                $status = 'intransit';
            }
            // order main status being finished or rejected
            if ($this->status == 'finished') {
                $status = 'finished';
            } elseif ($this->status == 'rejected') {
                $status = 'canceled';
            }
        } elseif ($this->type == 'google_places') {
            // delivery status is the order status
            if ($this->delivery_status == 'pending') {
                $status = 'pending';
            } elseif ($this->delivery_status == 'accepted') {
                $status = 'inprogress';
            } elseif ($this->delivery_status == 'reached_store') {
                $status = 'reached_store';
            }
            // invoice is the order status
            if ($this->have_invoice == 'true') {
                $status = 'invoice_created';
            }
            // order main status being finished or rejected
            if ($this->status == 'finished') {
                $status = 'finished';
            } elseif ($this->status == 'rejected') {
                $status = 'canceled';
            }
        } elseif ($this->type == 'google_places') {
            // delivery status is the order status
            if ($this->delivery_status == 'pending') {
                $status = 'pending';
            } elseif ($this->delivery_status == 'accepted') {
                $status = 'inprogress';
            } elseif ($this->delivery_status == 'reached_store') {
                $status = 'reached_store';
            }
            // invoice is the order status
            if ($this->have_invoice == 'true') {
                $status = 'invoice_created';
            }
            // order main status being finished or rejected
            if ($this->status == 'finished') {
                $status = 'finished';
            } elseif ($this->status == 'rejected') {
                $status = 'canceled';
            }
        } elseif ($this->type == 'parcel_delivery') {
            // delivery status is the order status
            if ($this->delivery_status == 'pending') {
                $status = 'pending';
            } elseif ($this->delivery_status == 'accepted') {
                $status = 'inprogress';
            } elseif ($this->delivery_status == 'reached_receive_location') {
                $status = 'reached_receive_location';
            } elseif ($this->delivery_status == 'reached_deliver_location') {
                $status = 'reached_deliver_location';
            }
            // order main status being finished or rejected
            if ($this->status == 'finished') {
                $status = 'finished';
            } elseif ($this->status == 'rejected') {
                $status = 'canceled';
            }
        } elseif ($this->type == 'immediate_consultants' || $this->type == 'lawyer_request') {
            // delivery status is the order status
            if ($this->delivery_status == 'pending') {
                $status = 'pending';

            } elseif ($this->delivery_status == 'accepted' && $this->payment_status == 'pending' && $this->payment_type != 'tabby') {
                $status = 'waiting_payment';
            } elseif ($this->delivery_status == 'accepted' && $this->payment_status == 'pending' && $this->payment_type == 'tabby') {
                $status = 'waiting_tabby_success';
            } elseif ($this->delivery_status == 'accepted' && $this->payment_status == 'success') {
                $status = 'inprogress';
            } elseif ($this->delivery_status == 'delivering') {
                $status = 'intransit';
            }
            // order main status being finished or rejected
            if ($this->status == 'finished') {
                $status = 'finished';
            } elseif ($this->status == 'rejected') {
                $status = 'canceled';
            }
        }

        return $status;
    }

    public function getInvoiceImagePathAttribute()
    {
        return $this->invoice_image ? asset('assets/uploads/invoices/' . $this->invoice_image) : '';
    }


//    public static function boot() {
//        parent::boot();
//        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */
//
//        self::deleted(function ($model) {
//            $model->deleteFile($model->attributes['invoice_image'], 'invoices');
//
//        });
//    }


    public function orderproducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function orderadditive()
    {
        return $this->hasMany(OrderProductAdditive::class, 'order_product_id');
    }

    public function images()
    {
        return $this->hasMany(OrderImage::class);
    }

    public function reports()
    {
        return $this->hasMany(OrderReport::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function deliveryOffers()
    {
        return $this->hasMany(DeliveryOffer::class);
    }

    public function withdrawReasons()
    {
        return $this->hasMany(WithdrawReason::class);
    }
}
