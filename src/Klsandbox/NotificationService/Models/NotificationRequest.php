<?php

namespace Klsandbox\NotificationService\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App;

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
 *
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
 *
 * @property integer $from_user_id
 * @property integer $to_user_id
 * @property-read \App\Models\User $toUser
 *
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereFromUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Klsandbox\NotificationService\Models\NotificationRequest whereToUserId($value)
 *
 * @property-read \Klsandbox\SiteModel\Site $site
 * @mixin \Eloquent
 */
class NotificationRequest extends Model
{
    use \Klsandbox\SiteModel\SiteExtensions;

    protected $fillable = ['target_id', 'route', 'channel', 'to_user_id'];

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

            if (!$item->target_id) {
                App::abort(500, 'Invalid Notification');

                return false;
            }

            return true;
        });
    }

    public function toUser()
    {
        return $this->belongsTo(config('auth.model'), 'to_user_id');
    }

    public function site()
    {
        return $this->belongsTo('Klsandbox\SiteModel\Site', 'site_id');
    }
}
