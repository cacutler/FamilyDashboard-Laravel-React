<?php
namespace App\Policies;
use App\Models\Event;
use App\Models\User;
class EventPolicy {
    /**
     * Anyone in the family can see events belonging to family members.
     */
    public function viewAny(User $user): bool {
        return true;
    }
    /**
     * A user may view an event if the creator is a family member.
     */
    public function view(User $user, Event $event): bool {
        return $user->isFamilyMember($event->user);
    }
    /**
     * Any authenticated user (parent or child) may create an event.
     */
    public function create(User $user): bool {
        return true;
    }
    /**
     * A user may update their own event.
     * Parents may also update any of their children's events.
     */
    public function update(User $user, Event $event): bool {
        if ($user->id === $event->user_id) {
            return true;
        }
        return $user->isParent() && $user->children->contains('id', $event->user_id);
    }
    /**
     * Same rules as update for deletion.
     */
    public function delete(User $user, Event $event): bool {
        return $this->update($user, $event);
    }
}