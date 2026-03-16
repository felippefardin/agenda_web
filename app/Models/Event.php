<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

protected $fillable = [
'user_id',
'title',
'description',
'date',
'time',
'priority',
'shared'
];

}
