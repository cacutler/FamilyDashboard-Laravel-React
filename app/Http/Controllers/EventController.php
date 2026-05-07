<?php
namespace App\Http\Controllers;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
class EventController extends Controller {
    /**
     * Return all events visible to the authenticated user (own events + family members' events).
     */
    public function index(Request $request): Response {
        Gate::authorize('viewAny', Event::class);
        $user = $request->user();
        $events = Event::with('user:id,name,username')->whereIn('user_id', $user->familyMemberIds())->orderBy('start_date')->orderBy('start_time')->get();
        return Inertia::render('events/index', ['events' => $events]);
    }
    public function show(Event $event): JsonResponse {
        Gate::authorize('view', $event);
        return response()->json($event->load('user:id,name,username'));
    }
    public function store(Request $request): JsonResponse {
        Gate::authorize('create', Event::class);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'gte:start_date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'description' => ['nullable', 'string']
        ]);
        $event = $request->user()->events()->create($validated);
        return response()->json($event, 201);
    }
    public function update(Request $request, Event $event): JsonResponse {
        Gate::authorize('update', $event);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'gte:start_date'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],
            'description' => ['nullable', 'string']
        ]);
        $event->fill($validated)->save();
        return response()->json($event);
    }
    public function destroy(Event $event): JsonResponse {
        Gate::authorize('delete', $event);
        $event->deleteOrFail();
        return response()->json(null, 204);
    }
}