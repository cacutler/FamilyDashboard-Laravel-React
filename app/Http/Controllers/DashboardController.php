<?php
namespace App\Http\Controllers;
use App\Models\Event;
use App\Models\ToDo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
class DashboardController extends Controller {
    public function __invoke(Request $request): Response {
        $user = $request->user();
        $userIds = collect($user->familyMemberIds())->toArray();
        Gate::authorize('viewAny', Event::class);
        Gate::authorize('viewAny', ToDo::class);
        $events = Event::with('user:id,name,username')->whereIn('user_id', $userIds)->where('start_date', '>=', now()->toDateString())->orderBy('start_date')->orderBy('start_time')->limit(5)->get();
        $todos = DB::table('to_dos')->leftJoin('users as assignedToUser', 'to_dos.assigned_to', '=', 'assignedToUser.id')->where('to_dos.completed', false)->where(function ($query) use ($userIds) {
            $query->whereIn('to_dos.created_by', $userIds)->orWhereIn('to_dos.assigned_to', $userIds);
        })->select(
            'to_dos.id',
            'to_dos.title',
            'to_dos.type',
            'to_dos.completed',
            DB::raw('assignedToUser.name as assignedTo_name')
        )->orderBy('to_dos.created_at', 'desc')->limit(5)->get()->map(fn ($row) => [
            'id' => $row->id,
            'title' => $row->title,
            'type' => $row->type,
            'completed' => (bool) $row->completed,
            'assignedTo' => ['name' => $row->assignedTo_name]
        ]);
        $familyMembers = User::query()->whereIn('id', $userIds)->select('id', 'name', 'username', 'status')->orderBy('name', 'asc')->get();
        return Inertia::render('dashboard', [
            'events' => $events,
            'todos' => $todos,
            'familyMembers' => $familyMembers,
            'stats' => [
                'events' => Event::whereIn('user_id', $userIds)->count(),
                'todos' => DB::table('to_dos')->where('completed', false)->where(function ($query) use ($userIds) {
                    $query->whereIn('created_by', $userIds)->orWhereIn('assigned_to', $userIds);
                })->count(),
                'family' => count($familyMembers)
            ]
        ]);
    }
}