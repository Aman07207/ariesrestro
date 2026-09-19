<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelAdmin\StaffRequest;
use App\Models\User;
use App\Services\HotelAdmin\StaffService;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function __construct(private readonly StaffService $staff)
    {
    }

    public function index()
    {
        return view('hoteladmin.staff.index', [
            'staff' => User::where('hotel_id', Auth::user()->hotel_id)->orderBy('name')->paginate(15),
        ]);
    }

    public function create()
    {
        return view('hoteladmin.staff.create');
    }

    public function store(StaffRequest $request)
    {
        $this->staff->create($request->validated());

        return redirect()->route('hoteladmin.staff.index')->with('status', 'Staff account created.');
    }

    public function edit(User $staff)
    {
        $this->staff->ensureSameHotel($staff);

        return view('hoteladmin.staff.edit', ['staffMember' => $staff]);
    }

    public function update(StaffRequest $request, User $staff)
    {
        $this->staff->update($staff, $request->validated());

        return redirect()->route('hoteladmin.staff.index')->with('status', 'Staff account updated.');
    }

    public function destroy(User $staff)
    {
        $this->staff->delete($staff);

        return redirect()->route('hoteladmin.staff.index')->with('status', 'Staff account removed.');
    }
}
