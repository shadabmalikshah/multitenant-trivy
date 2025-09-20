<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'name', 'image', 'description', 'release_date'
    ];
}
