<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    
    // This model represents a blog post in the application.

    protected $fillable = [
        'title',
        'body',
    ];

}