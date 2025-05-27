<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bike;

class BikeController extends Controller
{
    public function index(Request $request)
    {
        $bikes = Bike::with('user')->get(); // Mostrar todas, sin filtrar por estado
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
        $bike->estado = 'inactivo';
        $bike->save();
        return response()->json(['success' => true]);
    }

    public function activate($id)
    {
        $bike = Bike::findOrFail($id);
        $bike->estado = 'activo';
        $bike->save();
        return response()->json(['success' => true]);
    }

    public function inactivate($id)
    {
        $bike = Bike::findOrFail($id);
        $bike->estado = 'inactivo';
        $bike->save();
        return response()->json(['success' => true]);
    }
}
