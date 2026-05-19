<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ToDo;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
class TodoController extends Controller {
    /**
     * Return all todos the user created or is assigned to, plus any todos involving their children (for parents).
     */
    public function index(Request $request): Response {
        Gate::authorize('viewAny', ToDo::class);
        $user = $request->user();
        $userIds = collect($user->familyMemberIds())->toArray();
        $todos = DB::table('to_dos')->leftJoin('users as createdByUser', 'to_dos.created_by', '=', 'createdByUser.id')->leftJoin('users as assignedToUser', 'to_dos.assigned_to', '=', 'assignedToUser.id')->whereIn('to_dos.created_by', $userIds)->orWhereIn('to_dos.assigned_to', $userIds)->select(
            'to_dos.id',
            'to_dos.title',
            'to_dos.notes',
            'to_dos.type',
            'to_dos.completed',
            'to_dos.completed_at',
            'to_dos.created_by',
            'to_dos.assigned_to',
            'to_dos.created_at',
            'to_dos.updated_at',
            DB::raw('createdByUser.id as createdBy_id, createdByUser.name as createdBy_name, createdByUser.username as createdBy_username'),
            DB::raw('assignedToUser.id as assignedTo_id, assignedToUser.name as assignedTo_name, assignedToUser.username as assignedTo_username')
        )->orderBy('to_dos.created_at', 'desc')->get()->map(function($row) {
            return [
                'id' => $row->id,
                'title' => $row->title,
                'notes' => $row->notes,
                'type' => $row->type,
                'completed' => (bool)$row->completed,
                'completed_at' => $row->completed_at,
                'created_by' => $row->created_by,
                'assigned_to' => $row->assigned_to,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'createdBy' => ['id' => $row->createdBy_id, 'name' => $row->createdBy_name, 'username' => $row->createdBy_username],
                'assignedTo' => ['id' => $row->assignedTo_id, 'name' => $row->assignedTo_name, 'username' => $row->assignedTo_username]
            ];
        });
        $family = DB::table('users')->whereIn('id', $userIds)->select('id', 'name', 'username')->get()->toArray();
        return Inertia::render('todos/index', ['todos' => $todos, 'family' => $family]);
    }
    public function show(ToDo $todo): Response {
        Gate::authorize('view', $todo);
        $todoData = DB::table('to_dos')->leftJoin('users as createdByUser', 'to_dos.created_by', '=', 'createdByUser.id')->leftJoin('users as assignedToUser', 'to_dos.assigned_to', '=', 'assignedToUser.id')->where('to_dos.id', $todo->id)->select(
            'to_dos.id',
            'to_dos.title',
            'to_dos.notes',
            'to_dos.type',
            'to_dos.completed',
            'to_dos.completed_at',
            'to_dos.created_by',
            'to_dos.assigned_to',
            'to_dos.created_at',
            'to_dos.updated_at',
            DB::raw('createdByUser.id as createdBy_id, createdByUser.name as createdBy_name, createdByUser.username as createdBy_username'),
            DB::raw('assignedToUser.id as assignedTo_id, assignedToUser.name as assignedTo_name, assignedToUser.username as assignedTo_username')
        )->first();
        $formattedTodo = [
            'id' => $todoData->id,
            'title' => $todoData->title,
            'notes' => $todoData->notes,
            'type' => $todoData->type,
            'completed' => (bool)$todoData->completed,
            'completed_at' => $todoData->completed_at,
            'created_by' => $todoData->created_by,
            'assigned_to' => $todoData->assigned_to,
            'created_at' => $todoData->created_at,
            'updated_at' => $todoData->updated_at,
            'createdBy' => ['id' => $todoData->createdBy_id, 'name' => $todoData->createdBy_name, 'username' => $todoData->createdBy_username],
            'assignedTo' => ['id' => $todoData->assignedTo_id, 'name' => $todoData->assignedTo_name, 'username' => $todoData->assignedTo_username]
        ];
        return Inertia::render('todos/show', ['todo' => $formattedTodo]);
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