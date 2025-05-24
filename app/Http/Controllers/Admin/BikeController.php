<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bike;

class BikeController extends Controller
{
    public function index()
    {
        $bikes = Bike::with('user')->get();
        return view('admin.bikes', compact('bikes'));
    }

    public function update(Request $request, $id)
    {
        $bike = Bike::findOrFail($id);
        $bike->update($request->only(['brand', 'model', 'color']));
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $bike = Bike::findOrFail($id);
        $bike->delete();
        return response()->json(['success' => true]);
    }
}
