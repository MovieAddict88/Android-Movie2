<?php

namespace App\Livewire\Locations;

use Livewire\Component;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;

class LocationManager extends Component
{
    public $locations;
    public $name, $address, $latitude, $longitude, $radius_meters = 100;
    public $isEdit = false;
    public $selected_id;

    public function render()
    {
        $this->locations = Location::where('company_id', Auth::user()->company_id)->get();
        return view('livewire.locations.location-manager');
    }

    public function resetFields()
    {
        $this->name = '';
        $this->address = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->radius_meters = 100;
        $this->isEdit = false;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer',
        ]);

        Location::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_meters' => $this->radius_meters,
        ]);

        session()->flash('message', 'Location created successfully.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $location = Location::findOrFail($id);
        $this->selected_id = $id;
        $this->name = $location->name;
        $this->address = $location->address;
        $this->latitude = $location->latitude;
        $this->longitude = $location->longitude;
        $this->radius_meters = $location->radius_meters;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer',
        ]);

        $location = Location::findOrFail($this->selected_id);
        $location->update([
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_meters' => $this->radius_meters,
        ]);

        session()->flash('message', 'Location updated successfully.');
        $this->resetFields();
    }

    public function delete($id)
    {
        Location::find($id)->delete();
        session()->flash('message', 'Location deleted successfully.');
    }
}
