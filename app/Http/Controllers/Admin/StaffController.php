<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Staff\CreateStaffAction;
use App\Actions\Admin\Staff\DeleteStaffAction;
use App\Actions\Admin\Staff\UpdateStaffAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Staff\StoreStaffRequest;
use App\Http\Requests\Admin\Staff\UpdateStaffRequest;
use App\Models\Staff;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class StaffController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CreateStaffAction $createStaffAction,
        private readonly UpdateStaffAction $updateStaffAction,
        private readonly DeleteStaffAction $deleteStaffAction,
    ) {}

    /**
     * Display a listing of the people.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Staff::class);

        $staffs = Staff::query()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Staff/Index', [
            'staffs' => $staffs,
        ]);
    }

    /**
     * Show the form for creating a new people.
     */
    public function create(): Response
    {
        $this->authorize('create', Staff::class);

        return Inertia::render('Admin/Staff/Create');
    }

    /**
     * Store a newly created people in storage.
     */
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        try {
            $this->createStaffAction->execute(
                peopleData: $request->validated(),
                avatarFile: $request->file('avatar')
            );

            return redirect()
                ->route('admin.staff.index')
                ->with('success', 'Data people berhasil dibuat!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal membuat data people: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified people.
     */
    public function show(Staff $staff): Response
    {
        $this->authorize('view', $staff);

        return Inertia::render('Admin/Staff/Show', [
            'staff' => $staff,
        ]);
    }

    /**
     * Show the form for editing the specified people.
     */
    public function edit(Staff $staff): Response
    {
        $this->authorize('update', $staff);

        return Inertia::render('Admin/Staff/Edit', [
            'staff' => $staff,
        ]);
    }

    /**
     * Update the specified staff in storage.
     */
    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        try {
            $this->updateStaffAction->execute(
                staff: $staff,
                staffData: $request->validated(),
                avatarFile: $request->file('avatar')
            );

            return redirect()
                ->route('admin.staff.index')
                ->with('success', 'Data staff berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui data staff: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified staff from storage.
     */
    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorize('delete', $staff);

        try {
            $this->deleteStaffAction->execute($staff);

            return redirect()
                ->route('admin.staff.index')
                ->with('success', 'Data people berhasil dihapus!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menghapus data people: '.$e->getMessage()]);
        }
    }
}
