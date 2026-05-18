<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
class FamilyController extends Controller {
    /**
     * List the authenticated parent's children or the authenticated child's parents.
     */
    public function index(Request $request): Response {
        $user = $request->user();
        if ($user->isParent()) {
            $children = $user->children()->select('users.id', 'users.name', 'users.username', 'users.email', 'users.birthdate', 'users.status')->get();
            return Inertia::render('family/index', ['children' => $children]);
        }
        $parents = $user->parents()->select('users.id', 'users.name', 'users.username', 'users.email')->get();
        return Inertia::render('family/index', ['parents' => $parents]);
    }
    /**
     * Link an existing child account to this parent by username.
     * A child may have at most 2 parents.
     * POST /family/link
     * { "username": "child_username" }
     */
    public function link(Request $request): RedirectResponse {
        $request->validate(['username' => ['required', 'string', 'exists:users,username']]);
        $child = User::query()->where('username', $request->username)->firstOrFail();
        Gate::authorize('linkChild', $child);
        $request->user()->children()->attach($child->id);
        return redirect()->route('family.index')->with('success', "{$child->name} has been linked to your family.");
    }
    /**
     * Remove a child from this parent's family.
     * DELETE /family/{user}
     */
    public function unlink(Request $request, User $user): RedirectResponse {
        Gate::authorize('unlinkChild', $user);
        $request->user()->children()->detach($user->id);
        return redirect()->route('family.index')->with('success', "{$user->name} has been removed from your family.");
    }
    /**
     * List the parents linked to a child account (visible to the child and their parents).
     * GET /family/{user}/parents
     */
    public function parents(User $user): Response {
        Gate::authorize('view', $user);
        $parents = $user->parents()->select('users.id', 'users.name', 'users.username', 'users.email')->get();
        return Inertia::render('family/index', ['parents' => $parents]);
    }
    /**
     * List the children linked to a parent account.
     * GET /family/{user}/children
     */
    public function children(User $user): Response {
        Gate::authorize('view', $user);
        $children = $user->children()->select('users.id', 'users.name', 'users.username', 'users.email', 'users.birthdate', 'users.status')->get();
        return Inertia::render('family/index', ['children' => $children]);
    }
}