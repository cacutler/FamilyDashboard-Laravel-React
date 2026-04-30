<?php
namespace App\Policies;
use App\Models\ToDo;
use App\Models\User;
class TodoPolicy {
    /**
     * Any authenticated user can reach the todos index (queries are scoped per user in the controller).
     */
    public function viewAny(User $user): bool {
        return true;
    }
    /**
     * A user may view a todo if they created it or are assigned to it, OR if they are a parent and the todo involves one of their children.
     */
    public function view(User $user, ToDo $todo): bool {
        if (in_array($user->id, [$todo->created_by, $todo->assigned_to])) {
            return true;
        }
        if ($user->isParent()) {
            $childIds = $user->children->pluck('id');
            return $childIds->contains($todo->created_by) || $childIds->contains($todo->assigned_to);
        }
        return false;
    }
    /**
     * Only parents can create todos (they assign them to children or themselves).
     * Children cannot create todos for others.
     */
    public function create(User $user): bool {
        return $user->isParent();
    }
    /**
     * Parents can fully update any todo they created or that involves their children.
     * Children may only update 'notes' and 'completed' on todos assigned to them; this is enforced in the controller, not here.
     */
    public function update(User $user, ToDo $todo): bool {
        if ($user->id === $todo->created_by) {// Full edit: creator or a parent whose child is involved
            return true;
        }
        if ($user->isParent()) {
            $childIds = $user->children->pluck('id');
            return $childIds->contains($todo->created_by) || $childIds->contains($todo->assigned_to);
        }
        return $user->id === $todo->assigned_to;// Child assigned to this todo gets partial edit rights (notes + complete)
    }
    /**
     * Only the creator or a parent can delete a todo.
     */
    public function delete(User $user, ToDo $todo): bool {
        if ($user->id === $todo->created_by) {
            return true;
        }
        if ($user->isParent()) {
            return $user->children->contains('id', $todo->created_by);
        }
        return false;
    }
    /**
     * Dedicated gate for marking a todo complete/incomplete.
     * Both the assigned child AND the parent creator may toggle this.
     */
    public function complete(User $user, ToDo $todo): bool {
        return $this->update($user, $todo);
    }
}