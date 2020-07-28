<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
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

    public function orders() {
        return $this->hasMany('App\Model\Order\Order');
    }

    public function stockPositions() {
        return $this->hasMany('App\Model\Stock\Position\StockPosition');
    }

    public function stockDividendStatementLines() {
        return $this->hasMany('App\Model\Stock\Dividend\StockDividendStatementLine');
    }

    public function bondOrders() {
        return $this->hasMany('App\Model\Bond\BondOrder');
    }

    public function consolidatorStateMachine() {
        return $this->hasOne('App\Portfolio\Consolidator\ConsolidatorStateMachine');
    }
}
