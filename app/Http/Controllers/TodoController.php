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
    public function show(ToDo $todo): JsonResponse {
        Gate::authorize('view', $todo);
        return response()->json($todo->load('createdBy:id,name,username', 'assignedTo:id,name,username'));
    }
    /**
     * Only parents may create todos and may assign them to any family member.
     */
    public function store(Request $request): JsonResponse {
        Gate::authorize('create', ToDo::class);
        $user = $request->user();
        $familyIds = $user->familyMemberIds();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'type' => ['required', 'in:chore,reminder'],
            'assigned_to' => ['required', 'integer', 'in:' . implode(',', $familyIds)]
        ]);
        $todo = $user->todos()->create([...$validated, 'assigned_to' => $validated['assigned_to']]);
        return response()->json($todo, 201);
    }
    /**
     * Full update for parents; children may only update 'notes' and 'completed'.
     */
    public function update(Request $request, ToDo $todo): JsonResponse {
        Gate::authorize('update', $todo);
        $user = $request->user();
        $isCreator = $user->id === $todo->created_by;
        if ($user->isParent()  || $isCreator) {//Full edit
            $familyIds = $user->familyMemberIds();
            $validated = $request->validate([
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'notes' => ['nullable', 'string'],
                'type' => ['sometimes', 'required', 'in:chore,reminder'],
                'assigned_to' => ['sometimes', 'required', 'integer', 'in:' . implode(',', $familyIds)],
                'completed' => ['sometimes', 'boolean']
            ]);
        } else {//Child assigned to this todo: notes + completed only
            $validated = $request->validate([
                'notes' => ['nullable', 'string'],
                'completed' => ['sometimes', 'boolean']
            ]);
        }
        if (isset($validated['completed'])) {
            $validated['completed_at'] = $validated['completed'] ? now() : null;
        }
        $todo->fill($validated)->save();
        return response()->json($todo);
    }
    /**
     * Toggle completed status — available to assigned child or parent.
     */
    public function complete(ToDo $todo): JsonResponse {
        Gate::authorize('complete', $todo);
        $todo->fill([
            'completed' => !$todo->completed,
            'completed_at' => !$todo->completed ? now() : null
        ])->save();
        return response()->json($todo);
    }
    public function destroy(ToDo $todo): JsonResponse {
        Gate::authorize('delete', $todo);
        $todo->deleteOrFail();
        return response()->json(null, 204);
    }
}