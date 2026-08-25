<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\InteractsWithDeferredIndex;
use App\Http\Controllers\Concerns\InteractsWithResourceAbilities;
use App\Http\Controllers\Concerns\InteractsWithTrashedFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use InteractsWithDeferredIndex;
    use InteractsWithResourceAbilities;
    use InteractsWithTrashedFilter;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        return $this->renderDeferredIndex(
            'admin/users/Index',
            'users',
            fn (): LengthAwarePaginator => $this->usersPaginator($request),
            [
                'roles' => $this->roleOptions(),
                'filters' => [
                    'search' => (string) $request->string('search'),
                    'role' => (string) $request->string('role'),
                    'trashed' => $this->resolveTrashedFilter($request),
                    'per_page' => (string) $this->resolvePerPage($request),
                ],
                'can' => $this->resolveResourceAbilities(User::class),
            ],
        );
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private function usersPaginator(Request $request): LengthAwarePaginator
    {
        $search = (string) $request->string('search');
        $role = (string) $request->string('role');

        return $this->applyTrashedToQuery(User::query(), $this->resolveTrashedFilter($request))
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->when($search !== '', fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($search).'%'])))
            ->orderBy('name')
            ->paginate($this->resolvePerPage($request))
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
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(): array
    {
        return array_map(fn (UserRole $role): array => [
            'value' => $role->value,
            'label' => $role->label(),
        ], UserRole::cases());
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->validated());
        $user->forceFill(['email_verified_at' => now()])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.created')]);

        return back();
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.updated')]);

        return back();
    }

    /**
     * Soft delete on the first call; permanently delete when the user is
     * already trashed. Admins can never delete their own account.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        abort_if($request->user()->is($user), 422, __('app.admin.users.cannot_delete_self'));

        if ($user->trashed()) {
            $user->forceDelete();

            Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.force_deleted')]);

            return back();
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.deleted')]);

        return back();
    }

    public function restore(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.restored')]);

        return back();
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        Password::sendResetLink(['email' => $user->email]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.users.reset_link_sent')]);

        return back();
    }
}
