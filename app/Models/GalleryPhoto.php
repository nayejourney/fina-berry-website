<?php

namespace App\Models;

use App\Models\Concerns\HasImage;
use Illuminate\Database\Eloquent\Model;

class GalleryPhoto extends Model
{
    use HasImage;

    protected $fillable = ['image', 'caption', 'sort_order'];
}
