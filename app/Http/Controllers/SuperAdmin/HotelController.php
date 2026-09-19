<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreHotelRequest;
use App\Http\Requests\SuperAdmin\UpdateHotelRequest;
use App\Models\Hotel;
use App\Services\SuperAdmin\HotelService;

class HotelController extends Controller
{
    public function __construct(private readonly HotelService $hotels)
    {
    }

    public function index()
    {
        return view('superadmin.hotels.index', ['hotels' => Hotel::latest()->paginate(15)]);
    }

    public function create()
    {
        return view('superadmin.hotels.create');
    }

    public function store(StoreHotelRequest $request)
    {
        $data = $request->validated();
        $data['is_luxury_hotel'] = $request->boolean('is_luxury_hotel');
        unset($data['logo']);

        $this->hotels->create($data, $request->file('logo'));

        return redirect()->route('superadmin.hotels.index')->with('status', 'Hotel created.');
    }

    public function edit(Hotel $hotel)
    {
        return view('superadmin.hotels.edit', ['hotel' => $hotel]);
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel)
    {
        $data = $request->validated();
        $data['is_luxury_hotel'] = $request->boolean('is_luxury_hotel');
        unset($data['logo']);

        $this->hotels->update($hotel, $data, $request->file('logo'));

        return redirect()->route('superadmin.hotels.index')->with('status', 'Hotel updated.');
    }

    public function destroy(Hotel $hotel)
    {
        $this->hotels->deactivate($hotel);

        return redirect()->route('superadmin.hotels.index')->with('status', 'Hotel deactivated.');
    }
}
