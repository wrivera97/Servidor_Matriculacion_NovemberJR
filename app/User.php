<?php

namespace App;

use App\Notifications\ResetePasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable //implements Auditable
{
    // use \OwenIt\Auditing\Auditable;
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name', 'name', 'email', 'password','estado'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function carreras()
    {
        return $this->belongsToMany('App\Carrera')->withPivot('carrera_id')
            ->orderBy('descripcion');
    }

    public function estudiante()
    {
        return $this->hasOne('App\Estudiante');
    }

    public function sendPasswordResetNotification($token){
        $this->notify(new ResetePasswordNotification($token));
    }
}
