<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Fighting extends Model
{

    protected $fillable = [

        'tournament_id',
        'player1_id',
        'player2_id',
        'score_player1',
        'score_player2',
        'target_score',
        'status'

    ];



    public function tournament()
    {
        return $this->belongsTo(
            Tournament::class
        );
    }



    public function player1()
    {
        return $this->belongsTo(
            Player::class,
            'player1_id'
        );
    }



    public function player2()
    {
        return $this->belongsTo(
            Player::class,
            'player2_id'
        );
    }


}
