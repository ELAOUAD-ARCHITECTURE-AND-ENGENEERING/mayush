<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\Area;
use App\Models\City;
use App\Models\State;
use Auth;

class AddressController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $address = new Address;
        $address->user_id       = Auth::user()->id;
        $address->address       = $request->address;
        $address->country_id    = $request->country_id;
        $address->state_id      = $request->state_id ?? null;
        $address->city_id       = $request->city_id;
        $address->area_id       = $request->area_id ?? null;
        $address->longitude     = $request->longitude;
        $address->latitude      = $request->latitude;
        $address->postal_code   = $request->postal_code;
        $address->phone         = $request->phone;
        $address->save();

        return back();
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['address_data'] = $this->authorizedAddress($id);
        $data['states'] = State::where('status', 1)->where('country_id', $data['address_data']->country_id)->get();
        $data['cities'] = City::where('status', 1)->where('state_id', $data['address_data']->state_id)->get();
        $data['areas'] = Area::where('status', 1)->where('city_id', $data['address_data']->city_id)->get();

        $returnHTML = view('seller.profile.address_edit_modal', $data)->render();
        return response()->json(array('data' => $data, 'html'=>$returnHTML));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $address = $this->authorizedAddress($id);
        
        $address->address       = $request->address;
        $address->country_id    = $request->country_id;
        if ($request->country_id && !$request->state_id && $request->country_id != $address->country_id) {
            $address->state_id = null;
        } else {
            $address->state_id = $request->state_id ?? $address->state_id;
        }
        $address->city_id       = $request->city_id;
        $address->area_id       = $request->area_id ?? null;
        $address->longitude     = $request->longitude;
        $address->latitude      = $request->latitude;
        $address->postal_code   = $request->postal_code;
        $address->phone         = $request->phone;

        $address->save();

        flash(translate('Address info updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $address = $this->authorizedAddress($id);
        if(!$address->set_default){
            $address->delete();
            return back();
        }
        flash(translate('Default address cannot be deleted'))->warning();
        return back();
    }

    public function getStates(Request $request) {
        $states = State::where('status', 1)->where('country_id', $request->country_id)->get();
        $html = '<option value="">'.translate("Select State").'</option>';
        
        foreach ($states as $state) {
            $html .= '<option value="' . $state->id . '">' . $state->name . '</option>';
        }
        
        return response()->json($html);
    }
    
    public function getCities(Request $request) {
        $cities = City::where('status', 1)->where('state_id', $request->state_id)->get();
        $html = '<option value="">'.translate("Select City").'</option>';
        
        foreach ($cities as $row) {
            $html .= '<option value="' . $row->id . '">' . $row->getTranslation('name') . '</option>';
        }
        
        return response()->json($html);
    }

    public function set_default($id){
        $targetAddress = $this->authorizedAddress($id);

        foreach (Auth::user()->addresses as $key => $address) {
            $address->set_default = 0;
            $address->save();
        }
        $targetAddress->set_default = 1;
        $targetAddress->save();

        return back();
    }

    private function authorizedAddress($id): Address
    {
        $address = Address::findOrFail($id);

        if ((int) $address->user_id !== (int) Auth::id()) {
            abort(403);
        }

        return $address;
    }
}
