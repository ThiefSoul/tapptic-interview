<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Swipe extends Model
{
    use HasFactory;

    // TODO: it might be considered to extract it to ENUM added in PHP 8.1
    const ATTITUDE_LIKE = 'LIKE';
    const ATTITUDE_DISLIKE = 'DISLIKE';

    protected $fillable = ['receiver_id', 'attitude'];
}
