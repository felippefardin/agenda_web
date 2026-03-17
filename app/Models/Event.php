<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Event extends Model
{

protected $fillable = [
'user_id',
'title',
'description',
'date',
'time',
'priority',
'shared',
'status'
];

public function user()
{
    return $this->belongsTo(User::class);
}

}