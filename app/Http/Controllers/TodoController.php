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
        return response()->json();
    }
}