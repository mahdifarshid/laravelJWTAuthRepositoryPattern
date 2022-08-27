<?php

namespace App\Models;

use Carbon\Carbon;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Trez\RayganSms\Facades\RayganSms;

class Token extends Model
{
    //    use HasFactory;

    const EXPIRATION_TIME = 2;

    protected $fillable = [
        'code',
        'user_id',
        'used'
    ];

    public function __construct(array $attributes = [])
    {
        if (!isset($attributes['code'])) {
            $attributes['code'] = $this->generateCode();
        }

        parent::__construct($attributes);
    }

    /**
     * Generate a six digits code
     *
     * @param int $codeLength
     * @return string
     */
    public function generateCode($codeLength = 4)
    {
        $max = pow(10, $codeLength);
        $min = $max / 10 - 1;
        $code = mt_rand($min, $max);
        return $code;
    }

    /**
     * User tokens relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * True if the token is not used nor expired
     *
     * @return bool
     */
    public function isValid()
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    /**
     * Is the current token used
     *
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * Is the current token expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->created_at->diffInMinutes(Carbon::now()) > static::EXPIRATION_TIME;
    }

    public function sendCode($mobile)
    {
        // if (! $this->user) {
        //     throw new \Exception("No user attached to this token.");
        // }

        // if (! $this->code) {
        //     $this->code = $this->generateCode();
        // }

        try {
            //             RayganSms::sendAuthCode($mobile,' به وب سایت خوش آمدید...
            //  کد ورود شما '.$this->code.' می باشد',false);

            $sms = new \Trez\RayganSms\Sms('farshid7720', "73838يَصا8", '50002910001080');
            $sms->sendAuthCode('09033845195');
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

        return true;
    }



    public function verify($mobile, $code)
    {
        $sms = new \Trez\RayganSms\Sms('farshid7720', "73838يَصا8", '50002910001080');
        return $sms->checkAuthCode($mobile, $code);
    }
}
