<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as AuthCanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject, AuthCanResetPassword
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable; 

    const ADMIN = 255;

    const API_USER = 1;
    const SHARE_USER = 2;
    const UNAUTHORIZED_USER = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPassword($token));
    }

    public static function resetUrl()
    {
        return '/reset';
    }

    public static function getAuthenticationType(): int
    {
        if (Auth::guard('api')->check()) {
            return self::API_USER;
        } else if (Auth::guard('share')->check()) {
            return self::SHARE_USER;
        }
        return self::UNAUTHORIZED_USER;
    }

}
