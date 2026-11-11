<?php

namespace App\Models\Concerns;

/** Menyediakan accessor image_url untuk model dengan kolom `image`. */
trait HasImage
{
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, 'uploads/')
            ? asset('storage/'.$this->image)
            : asset($this->image);
    }
}
