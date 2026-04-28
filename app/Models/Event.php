<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['name', 'location', 'start_date', 'end_date', 'start_time', 'end_time', 'description'])]
class Event extends Model {
    use HasFactory;
    protected function casts(): array {
        return [
            //'start_time' => 'datetime:H:i:s', //For Carbon support
            //'end_time'   => 'datetime:H:i:s', //For Carbon support
            'start_date' => 'date',
            'end_date' => 'date'
        ];
    }
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}