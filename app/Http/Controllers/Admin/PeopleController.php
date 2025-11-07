<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\People\CreatePeopleAction;
use App\Actions\Admin\People\DeletePeopleAction;
use App\Actions\Admin\People\UpdatePeopleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\People\StorePeopleRequest;
use App\Http\Requests\Admin\People\UpdatePeopleRequest;
use App\Models\People;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PeopleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CreatePeopleAction $createPeopleAction,
        private readonly UpdatePeopleAction $updatePeopleAction,
        private readonly DeletePeopleAction $deletePeopleAction,
    ) {}

    /**
     * Display a listing of the people.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', People::class);

        $peoples = People::query()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/People/Index', [
            'peoples' => $peoples,
        ]);
    }

    /**
     * Show the form for creating a new people.
     */
    public function create(): Response
    {
        $this->authorize('create', People::class);

        return Inertia::render('Admin/People/Create');
    }

    /**
     * Store a newly created people in storage.
     */
    public function store(StorePeopleRequest $request): RedirectResponse
    {
        try {
            $this->createPeopleAction->execute(
                peopleData: $request->validated(),
                avatarFile: $request->file('avatar')
            );

            return redirect()
                ->route('admin.people.index')
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
    public function show(People $people): Response
    {
        $this->authorize('view', $people);

        return Inertia::render('Admin/People/Show', [
            'people' => $people,
        ]);
    }

    /**
     * Show the form for editing the specified people.
     */
    public function edit(People $people): Response
    {
        $this->authorize('update', $people);

        return Inertia::render('Admin/People/Edit', [
            'people' => $people,
        ]);
    }

    /**
     * Update the specified people in storage.
     */
    public function update(UpdatePeopleRequest $request, People $people): RedirectResponse
    {
        try {
            $this->updatePeopleAction->execute(
                people: $people,
                peopleData: $request->validated(),
                avatarFile: $request->file('avatar')
            );

            return redirect()
                ->route('admin.people.index')
                ->with('success', 'Data people berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui data people: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified people from storage.
     */
    public function destroy(People $people): RedirectResponse
    {
        $this->authorize('delete', $people);

        try {
            $this->deletePeopleAction->execute($people);

            return redirect()
                ->route('admin.people.index')
                ->with('success', 'Data people berhasil dihapus!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menghapus data people: '.$e->getMessage()]);
        }
    }
}
