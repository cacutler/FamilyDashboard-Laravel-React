<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
    public function link(Request $request): JsonResponse {
        $request->validate(['username' => ['required', 'string', 'exists:users,username']]);
        $child = User::query()->where('username', $request->username)->firstOrFail();
        Gate::authorize('linkChild', $child);
        $request->user()->children()->attach($child->id);
        return response()->json([
            'message' => "{$child->name} has been linked to your family.",
            'child' => $child->only('id', 'name', 'username', 'email')
        ]);
    }
    /**
     * Remove a child from this parent's family.
     * DELETE /family/{user}
     */
    public function unlink(Request $request, User $user): JsonResponse {
        Gate::authorize('unlinkChild', $user);
        $request->user()->children()->detach($user->id);
        return response()->json(['message' => "{$user->name} has been removed from your family."]);
    }
    /**
     * List the parents linked to a child account (visible to the child and their parents).
     * GET /family/{user}/parents
     */
    public function parents(User $user): JsonResponse {
        Gate::authorize('view', $user);
        $parents = $user->parents()->select('users.id', 'users.name', 'users.username', 'users.email')->get();
        return response()->json($parents);
    }
}