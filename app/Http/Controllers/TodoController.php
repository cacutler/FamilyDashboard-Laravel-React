<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ToDo;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
class TodoController extends Controller {
    /**
     * Return all todos the user created or is assigned to, plus any todos involving their children (for parents).
     */
    public function index(Request $request): Response {
        Gate::authorize('viewAny', ToDo::class);
        $user = $request->user();
        $userIds = collect($user->familyMemberIds())->toArray();
        $todos = ToDo::with(['createdBy:id,name,username', 'assignedTo:id,name,username'])->where(fn($q) => $q->whereIn('created_by', $userIds)->orWhereIn('assigned_to', $userIds, 'and', false))->orderBy('created_at', 'desc')->get();
        return Inertia::render('todos/index', ['todos' => $todos, 'family' => $user->familyMemberIds()]);
    }
    public function show(ToDo $todo): Response {
        Gate::authorize('view', $todo);
        return Inertia::render('todos/show', ['todo' => $todo->load('createdBy:id,name,username', 'assignedTo:id,name,username')]);
    }
    /**
     * Only parents may create todos and may assign them to any family member.
     */
    public function store(Request $request): RedirectResponse {
        Gate::authorize('create', ToDo::class);
        $user = $request->user();
        $familyIds = $user->familyMemberIds();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'type' => ['required', 'in:chore,reminder'],
            'assigned_to' => ['required', 'integer', 'in:' . implode(',', $familyIds)]
        ]);
        $todo = $user->todos()->create($validated);
        return redirect()->route('todos.show', $todo);
    }
    /**
     * Full update for parents; children may only update 'notes' and 'completed'.
     */
    public function update(Request $request, ToDo $todo): RedirectResponse {
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
        return redirect()->route('todos.show', $todo);
    }
    /**
     * Toggle completed status — available to assigned child or parent.
     */
    public function complete(ToDo $todo): RedirectResponse {
        Gate::authorize('complete', $todo);
        $todo->fill([
            'completed' => !$todo->completed,
            'completed_at' => !$todo->completed ? now() : null
        ])->save();
        return redirect()->route('todos.show', $todo);
    }
    public function destroy(ToDo $todo): RedirectResponse {
        Gate::authorize('delete', $todo);
        $todo->deleteOrFail();
        return redirect()->route('todos.index');
    }
}