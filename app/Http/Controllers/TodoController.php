<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ToDo;
use Illuminate\Support\Facades\Gate;
class TodoController extends Controller {
    /**
     * Return all todos the user created or is assigned to, plus any todos involving their children (for parents).
     */
    public function index(Request $request): JsonResponse {
        Gate::authorize('viewAny', ToDo::class);
        $user = $request->user();
        $userIds = $user->familyMemberIds();
        $todos = ToDo::with(['createdBy:id,name,username', 'assignedTo:id,name,username'])->where(function ($q) use ($user, $userIds) {
            $q->whereIn('created_by', $userIds)->orWhereIn('assigned_to', $userIds);
        })->orderBy('created_at', 'desc')->get();
        return response()->json($todos);
    }
}