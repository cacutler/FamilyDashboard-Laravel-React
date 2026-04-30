<?php
namespace App\Policies;
use App\Models\User;
class UserPolicy {
    public function viewAny(User $user): bool {
        return true;
    }
    /**
     * A user may view another profile if they are family members.
     */
    public function view(User $user, User $target): bool {
        return $user->id === $target->id || $user->isFamilyMember($target);
    }
    /**
     * A parent may link a child account that has fewer than 2 parents.
     */
    public function linkChild(User $user, User $target): bool {
        return $user->isParent() && $target->isChild() && !$target->parents->contains('id', $user->id) && $target->parents->count() < 2;
    }
    /**
     * A parent may unlink a child that is currently linked to them.
     */
    public function unlinkChild(User $user, User $target): bool {
        return $user->isParent() && $target->parents->contains('id', $user->id);
    }
    /**
     * A user may update their own profile.
     * A parent may also update any of their linked children's profiles.
     */
    public function update(User $user, User $target): bool {
        if ($user->id === $target->id) {
            return true;
        }
        return $user->isParent() && $user->children->contains('id', $target->id);
    }
    /**
     * A user may delete their own account.
     * A parent may delete a child's account only if they are the sole parent.
     */
    public function delete(User $user, User $target): bool {
        if ($user->id === $target->id) {
            return true;
        }
        return $user->isParent() && $user->children->contains('id', $target->id);
    }
}