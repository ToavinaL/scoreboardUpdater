<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Player extends Model
{

    protected $fillable = [
        'nickname',
        'real_name',
        'country',
        'team',
        'avatar'
    ];


    public function fighting()
    {
        return $this->hasMany(
            Fighting::class,
            'player1_id'
        );
    }

}
