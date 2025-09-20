<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'db', 'admin_email', 'admin_password'
    ];
    public $timestamps = false;
}
