<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This protects against mass-assignment vulnerabilities. Only these fields can be filled using create() or update().
     * @var array
     */

    protected $fillable = [
    'title',
    'body',
    'image',
];

}