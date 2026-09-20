<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StaffRequest;
use App\Models\Hotel;
use App\Models\User;
use App\Services\SuperAdmin\StaffService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(private readonly StaffService $staff)
    {
    }

    public function index(Request $request)
    {
        $staff = User::with('hotel')
            ->whereNotNull('hotel_id')
            ->when($request->filled('hotel'), fn ($q) => $q->where('hotel_id', $request->integer('hotel')))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('employee_id', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('superadmin.staff.index', [
            'staff' => $staff,
            'hotels' => Hotel::orderBy('name')->get(['id', 'name']),
            'roles' => [UserRole::HotelAdmin, UserRole::Manager, UserRole::Waiter, UserRole::Chef],
        ]);
    }

    public function create()
    {
        return view('superadmin.staff.create', ['hotels' => Hotel::orderBy('name')->get(['id', 'name'])]);
    }

    public function store(StaffRequest $request)
    {
        $this->staff->create($request->validated());

        return redirect()->route('superadmin.staff.index')->with('status', 'Staff account created.');
    }

    public function edit(User $staff)
    {
        return view('superadmin.staff.edit', [
            'staffMember' => $staff,
            'hotels' => Hotel::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(StaffRequest $request, User $staff)
    {
        $this->staff->update($staff, $request->validated());

        return redirect()->route('superadmin.staff.index')->with('status', 'Staff account updated.');
    }

    public function destroy(User $staff)
    {
        $this->staff->delete($staff);

        return redirect()->route('superadmin.staff.index')->with('status', 'Staff account removed.');
    }
}
