<?php
namespace App\Models;
use App\ToDoType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[Fillable(['title', 'notes', 'type'])]
class ToDo extends Model {
    use HasFactory;
    protected function casts(): array {
        return ['type' => ToDoType::class];
    }
    public function createdBy(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function assignedTo(): BelongsTo {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}