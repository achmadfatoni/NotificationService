<?php

namespace Klsandbox\NotificationService\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Auth;

/**
 * Klsandbox\NotificationService\Models\NotificationRequest
 *
 * @property integer $site_id
 * @property integer $user_id
 * @property integer $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property integer $target_id
 * @property string $route
 * @property string $channel
 * @property boolean $sent
 * @property string $response_text
 * @property integer $response_code
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereSiteId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereTargetId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereRoute($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereChannel($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereSent($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereResponseText($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereResponseCode($value)
 * @property integer $from_user_id
 * @property integer $to_user_id
 * @property-read \App\Models\User $toUser
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereFromUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereToUserId($value)
 * @property-read \Klsandbox\SiteModel\Site $site
 * @mixin \Eloquent
 * @property-read \App\Models\User $fromUser
 * @property integer $to_customer_id
 * @property-read \App\Models\Customer $toCustomer
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereToCustomerId($value)
 */
class NotificationRequest extends Model
{
    protected $fillable = ['target_id', 'route', 'channel', 'to_user_id', 'to_customer_id'];

    //

    public static function boot()
    {
        parent::boot();

        self::creating(function ($item) {
            if (Auth::user()) {
                $item->from_user_id = Auth::user()->id;
            } else {
                $userClass = config('auth.model');
                $item->from_user_id = $userClass::admin()->id;
            }

            assert($item->target_id);
            assert($item->to_user_id);

            return true;
        });
    }

    public function toUser()
    {
        return $this->belongsTo(config('auth.model'), 'to_user_id');
    }

    public function toCustomer()
    {
        return $this->belongsTo(Customer::class, 'to_customer_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(config('auth.model'), 'from_user_id');
    }

}
