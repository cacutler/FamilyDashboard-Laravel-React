<?php
namespace App\Models;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
#[Fillable(['name', 'username', 'email', 'password', 'status', 'birthdate'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable {
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    /**
     * Get the attributes that should be cast.
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime'
        ];
    }
    public function todos(): HasMany {
        return $this->hasMany(ToDo::class, 'created_by');
    }
    public function assignedTodos(): HasMany {
        return $this->hasMany(ToDo::class, 'assigned_to');
    }
    public function events(): HasMany {
        return $this->hasMany(Event::class);
    }
    public function parents(): BelongsToMany {
        return $this->belongsToMany(User::class, 'parent_user', 'child_id', 'parent_id');
    }
    public function children(): BelongsToMany {
        return $this->belongsToMany(User::class, 'parent_user', 'parent_id', 'child_id');
    }
    public function isParent(): bool {
        return $this->status === 'parent';
    }
    public function isChild(): bool {
        return $this->status === 'child';
    }
    /**
     * Returns true if $user is in the same family as this user.
     * Covers: self, own children, own parents, and siblings (shared parent).
     */
    public function isFamilyMember(User $user): bool {
        if ($this->id === $user->id) {
            return true;
        }
        if ($this->children->contains('id', $user->id)) {// Parents checking one of their children
            return true;
        }
        if ($this->parents->contains('id', $user->id)) {
            return true;
        }
        $myParentIds    = $this->parents->pluck('id');// Siblings share at least one common parent
        $theirParentIds = $user->parents->pluck('id');
        return $myParentIds->intersect($theirParentIds)->isNotEmpty();
    }
    /**
     * Returns the IDs of all family members visible to this user.
     * Parents see themselves + all their children.
     * Children see themselves + their parents + siblings.
     */
    public function familyMemberIds(): array {
        $ids = collect([$this->id]);
        if ($this->isParent()) {
            $ids = $ids->merge($this->children->pluck('id'));
        } else {
            $parentIds = $this->parents->pluck('id');
            $ids = $ids->merge($parentIds);
            if ($parentIds->isNotEmpty()) {
                $siblingIds = User::query()->whereHas('parents', function ($q) use ($parentIds) {// Siblings: other children of any of this user's parents
                    $q->whereIn('parent_user.parent_id', $parentIds);
                })->where('id', '!=', $this->id)->pluck('id');
                $ids = $ids->merge($siblingIds);
            }
        }
        return $ids->unique()->all();
    }
}