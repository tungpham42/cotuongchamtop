<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puzzle extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;
    
    public $fillable = [
        'name',
        'fen',
        'rating',
        'updated_at',
    ];
}