<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->string('search');
        $filter = (string) $request->string('filter');

        $users = User::query()
            ->withTrashed()
            ->when($filter === 'trashed', fn ($query) => $query->whereNotNull('deleted_at'))
            ->when($filter !== 'trashed', fn ($query) => $query->whereNull('deleted_at'))
            ->when($search !== '', fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($search).'%'])))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role->value,
                'roleLabel' => $user->role->label(),
                'createdAt' => $user->created_at?->toIso8601String(),
                'deleted' => $user->trashed(),
            ]);

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search !== '' ? $search : null,
                'filter' => $filter !== '' ? $filter : null,
            ],
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->validated());
        $user->forceFill(['email_verified_at' => now()])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.created')]);

        return back();
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.updated')]);

        return back();
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, __('app.admin.users.cannot_delete_self'));

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.deleted')]);

        return back();
    }

    public function restore(User $user): RedirectResponse
    {
        $user->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.restored')]);

        return back();
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        Password::sendResetLink(['email' => $user->email]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.reset_link_sent')]);

        return back();
    }
}
