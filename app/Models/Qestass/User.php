<?php

namespace App\Models\Qestass;

use App\Traits\DeviceTrait;
use App\Traits\ReportTrait;
use App\Traits\SmsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\HasApiTokens;
use App\Traits\Uploadable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SMS;
use Filament\Models\Contracts\HasName;


class User extends Authenticatable implements HasName
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['first_name', 'last_name', 'email', 'country_key', 'phone', 'changed_phone', 'block', 'password', 'email_verified_at', 'phone_verified_at', 'avatar', 'status', 'active', 'gender', 'completed_info', 'type', 'code', 'pin_code', 'lat', 'long', 'address', 'wallet', 'total_bills', 'total_delivery_fees', 'num_orders', 'num_comments', 'num_rating', 'rate', 'parent_id', 'approve', 'bank_iban_number', 'specialist_type', 'available', 'bank_name', 'bank_account_number', 'experience_year', 'identity_number', 'bio', 'work_license', 'connected', 'stage', 'app_commission', 'nafath_accepted', 'license_number', 'license_start_date', 'license_expire_date', 'appointmentBookingType'];

    protected $connection = 'qestass_app';
    const ADMIN_ID = 1;
    const ADMIN_TYPE = 'admin';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];


    public function getFullPhoneAttribute()
    {
        return '0' . $this->phone;
    }

    public function scopeSearch($query, $searchArray = [])
    {
        $query->where(function ($query) use ($searchArray) {
            if ($searchArray) {
                foreach ($searchArray as $key => $value) {
                    if (str_contains($key, '_id')) {
                        if (null != $value) {
                            $query->Where($key, $value);
                        }
                    } elseif ('order' == $key) {
                    } elseif ('created_at_min' == $key) {
                        if (null != $value) {
                            $query->WhereDate('created_at', '>=', $value);
                        }
                    } elseif ('created_at_max' == $key) {
                        if (null != $value) {
                            $query->WhereDate('created_at', '<=', $value);
                        }
                    } else {
                        if (null != $value) {
                            $query->Where($key, 'like', '%' . $value . '%');
                        }
                    }
                }
            }
        });
        return $query->orderBy('created_at', request()->searchArray && request()->searchArray['order'] ? request()->searchArray['order'] : 'DESC');
    }


    public function getFullChangedPhoneAttribute()
    {
        return '0' . $this->changed_phone;
    }

    public function getAvatarPathAttribute()
    {
        return $this->avatar ? asset('assets/uploads/users/' . $this->avatar) : '';
    }


    public function setAvatarAttribute($value)
    {
        if ($value != 'default.png') {
            if ($this->avatar != 'default.png' and $this->avatar) {
                File::delete(public_path('assets/uploads/users/' . $this->avatar));
            }
            $this->attributes['avatar'] = $this->uploadFile($value, 'users', true, 250, null);
        }

        //            File::delete(public_path('assets/uploads/users/' . $this->avatar));
        //            $this->attributes['avatar'] = $this->uploadFile($value, 'users', true, 250, null);

    }

    public function getWorkLicenseAttribute()
    {
        if (isset($this->attributes['work_license']))
            return $this->attributes['work_license'] ? asset('assets/uploads/users/' . $this->attributes['work_license']) : '';
    }


    public function setWorkLicenseAttribute($value)
    {
        if ($value != 'default.png') {
            if (isset($this->attributes['work_license']) && $this->attributes['work_license'] != 'default.png' and $this->attributes['work_license']) {
                File::delete(public_path('assets/uploads/users/' . $this->attributes['work_license']));
            }
            $this->attributes['work_license'] = $this->uploadFile($value, 'users', true, 250, null);
        }
    }

    public static function boot()
    {
        parent::boot();
        /* creating, created, updating, updated, deleting, deleted, forceDeleted, restored */

        self::deleted(function ($model) {
            $model->deleteFile($model->attributes['avatar'], 'users');
        });
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function complain()
    {
        return $this->hasMany(Complain::class);
    }

    //     public function notifications()
    //     {
    //         return $this->hasMany(Notification::class);
    //     }

    public function devices()
    {
        return $this->hasMany(userDevices::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function delegateJoinRequests()
    {
        return $this->hasMany(DelegateJoinrequest::class);
    }

    public function markAsActive()
    {
        $this->update(['status' => 'active']);
    }

    public function confirmChangePhone()
    {
        $this->update(['phone' => $this->changed_phone, 'changed_phone' => NULL]);
    }

    public function userOrders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function lawyerOrders()
    {
        return $this->hasMany(Order::class, 'lawyer_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function myReviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'seconduser_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function times()
    {
        return $this->hasMany(Time::class);
    }

    public function deliveryOffers()
    {
        return $this->hasMany(DeliveryOffer::class);
    }

    public function delegateCompany()
    {
        return $this->hasOne(DelegateCompany::class);
    }

    public function rating()
    {
        $reviewsCount = $this->reviews()->count();
        if ($reviewsCount == 0) {
            return number_format(0, 2);
        }
        $reviews = $this->reviews;
        $sum = 0;
        foreach ($reviews as $review) {
            $sum += $review->rate;
        }
        return number_format(round($sum / $reviewsCount, 2), 2);
    }

    public static function cleanPhone($phone)
    {
        $filteredPhone = [];
        if (substr($phone, 0, 4) == '+966') {

            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            $filteredPhone = [
                'country_key' => '00966',
                'phone' => substr($string, 4),
            ];
            return $filteredPhone;
        } elseif (substr($phone, 0, 5) == '00966') {
            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            $filteredPhone = [
                'country_key' => '00966',
                'phone' => substr($string, 5),
            ];
            return $filteredPhone;
        } elseif (substr($phone, 0, 3) == '966') {
            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            $filteredPhone = [
                'country_key' => '00966',
                'phone' => substr($string, 3),
            ];
            return $filteredPhone;
            // Egypt Phone
        } elseif (substr($phone, 0, 2) == '+2') {

            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            $filteredPhone = [
                'country_key' => '002',
                'phone' => substr($string, 3)
            ];
            return $filteredPhone;
        } elseif (substr($phone, 0, 3) == '002') {
            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            // return substr($string, 3);
            $filteredPhone = [
                'country_key' => '002',
                'phone' => substr($string, 4)
            ];
            return $filteredPhone;
        } elseif (substr($phone, 0, 1) == '2') {
            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            $filteredPhone = [
                'country_key' => '002',
                'phone' => substr($string, 2),
            ];
            return $filteredPhone;
        } elseif (substr($phone, 0, 2) == '01') {
            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            $filteredPhone = [
                'country_key' => '002',
                'phone' => substr($string, 1),
            ];
            return $filteredPhone;
        } elseif (substr($phone, 0, 1) == '0') {
            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            $filteredPhone = [
                'country_key' => '002',
                'phone' => substr($string, 1),
            ];
            return $filteredPhone;
        } else {
            $string = str_replace(' ', '', $phone);
            $string = str_replace('-', '', $string);

            $filteredPhone = [
                'country_key' => '00966',
                'phone' => $string,
            ];
            return $filteredPhone;
        }
    }

    //    public function sendVerificationCode($has_changed_phone = 'false')
    //    {
    //       $code = mt_rand(111111, 999999);
    //        $code = '123456';
    //        $data['code'] = $code;
    //
    //        // Mail::to($this->email)->send(new PassCode($code));
    //
    //        $this->update(['code' => $code]);
    //    }

    public function getFullSmsPhoneAttribute()
    {
        return $this->attributes['country_key'] . $this->attributes['phone'];
    }

    public function sendVerificationCode($has_changed_phone = 'false')
    {
        $code = mt_rand(111111, 999999);
        //        $code = '123456';
        $package = SMS::where('active', '1')->first();

        // Mail::to($this->email)->send(new PassCode($code));

        $data['code'] = $code;
        $msg = trans('auth.yourcode') . ' : ' . $code . ',' . trans('auth.qestats');
        $this->sendSms($this->FullSmsPhone, $msg, "taqnyat", $package);
        $this->update(['code' => $code]);
    }


    public function resetCode($password)
    {
        $this->update([
            'code' => null,
            'password' => $password
        ]);
    }

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setPasswordAttribute($value)
    {
        if ($value != null) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function withdrawReasons()
    {
        return $this->hasMany(WithdrawReason::class);
    }

    public function images()
    {
        return $this->hasMany(UserImage::class);
    }

    public function categories()
    {
        return $this->belongsToMany(\App\Models\Category::class, 'user_categories', 'user_id', 'category_id');
    }

    public function consultants()
    {
        return $this->hasMany(UserCosultant::class);
    }

    public function setPhoneAttribute($value)
    {
        $newValue = isset($value) && $value ? $value : $this->phone;
        $this->attributes['phone'] = $newValue;
    }

    // Lawyer Admin Relationships (Cross-database connections)
    public function caseRecords()
    {
        $defaultDb = config('database.connections.' . config('database.default') . '.database');
        return $this->hasMany(\App\Models\CaseRecord::class, 'client_id', 'id')
            ->from($defaultDb . '.case_records');
    }

    public function payments()
    {
        $defaultDb = config('database.connections.' . config('database.default') . '.database');
        return $this->hasMany(\App\Models\Payment::class, 'client_id', 'id')
            ->from($defaultDb . '.payments');
    }

    public function visits()
    {
        $defaultDb = config('database.connections.' . config('database.default') . '.database');
        return $this->hasMany(\App\Models\Visit::class, 'client_id', 'id')
            ->from($defaultDb . '.visits');
    }

    public function getFilamentName(): string
    {
        // Return the full name if both first_name and last_name are available
        if (!empty($this->first_name) && !empty($this->last_name)) {
            return trim($this->first_name . ' ' . $this->last_name);
        }

        // If only first_name is available
        if (!empty($this->first_name)) {
            return trim($this->first_name);
        }

        // If only last_name is available
        if (!empty($this->last_name)) {
            return trim($this->last_name);
        }

        // Fallback to email if no name is available
        return $this->email ?? 'Unknown User';
    }
}
