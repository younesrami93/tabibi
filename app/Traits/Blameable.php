<?php


namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

trait Blameable
{
    public static function bootBlameable()
    {
        // When creating a new record...
        static::creating(function ($model) {
            // If the model doesn't have a creator set yet...
            if (!$model->created_by) {
                // Set it to the current logged-in user (if exists)
                $model->created_by = Auth::id();
            }
        });
    }

    /**
     * Relationship: Get the User who created this.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withDefault(['name' => 'System/Unknown']);
    }
}