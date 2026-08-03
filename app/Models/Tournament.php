<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Tournament extends Model
{

    protected $fillable = [
        'name',
        'game',
        'format',
        'date'
    ];



    public function fightings()
    {
        return $this->hasMany(
            Fighting::class
        );
    }

}