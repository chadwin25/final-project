<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $showArchived = $request->query('view') === 'archived';

        $staffMembers = User::query()
            ->when($showArchived, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($matching) use ($search) {
                    $matching->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('full_name')
            ->paginate(12)
            ->withQueryString();

        return view('staff.index', compact('staffMembers', 'search', 'showArchived'));
    }

    public function create(): View
    {
        return view('staff.form', [
            'staff' => new User(),
        ]);
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['avatar', 'password_confirmation']);
        $newAvatar = null;

        try {
            if ($request->hasFile('avatar')) {
                $newAvatar = $request->file('avatar')->store('avatars', 'public');

                if ($newAvatar === false) {
                    throw new \RuntimeException('The avatar could not be saved.');
                }

                $data['avatar'] = $newAvatar;
            }

            User::create($data);
        } catch (Throwable $exception) {
            if (is_string($newAvatar)) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $exception;
        }

        return redirect()->route('staff.index')
            ->with('status', 'Staff account added.');
    }

    public function edit(User $staff): View
    {
        return view('staff.form', compact('staff'));
    }

    public function update(StaffRequest $request, User $staff): RedirectResponse
    {
        $data = $request->safe()->except(['avatar', 'password_confirmation']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $oldAvatar = $staff->avatar;
        $newAvatar = null;

        try {
            if ($request->hasFile('avatar')) {
                $newAvatar = $request->file('avatar')->store('avatars', 'public');

                if ($newAvatar === false) {
                    throw new \RuntimeException('The avatar could not be saved.');
                }

                $data['avatar'] = $newAvatar;
            }

            $staff->update($data);
        } catch (Throwable $exception) {
            if (is_string($newAvatar)) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $exception;
        }

        if (is_string($newAvatar) && $oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return redirect()->route('staff.index')
            ->with('status', 'Staff account updated.');
    }

    public function destroy(Request $request, User $staff): RedirectResponse
    {
        if ($staff->is($request->user())) {
            return back()->withErrors([
                'staff' => 'You cannot archive your own account while signed in.',
            ]);
        }

        $staff->delete();

        return redirect()->route('staff.index')
            ->with('status', 'Staff account archived. Past sales remain recorded.');
    }

    public function restore(int $staff): RedirectResponse
    {
        User::onlyTrashed()->findOrFail($staff)->restore();

        return redirect()->route('staff.index', ['view' => 'archived'])
            ->with('status', 'Staff account restored.');
    }
}