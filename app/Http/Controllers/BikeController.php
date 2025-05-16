<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use Illuminate\Http\Request;

class BikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bikes = Bike::where('user_id', auth()->id())->get();
        return view('bikes.index', compact('bikes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bikes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'color' => 'required|string|max:255',
        ]);
        $validated['user_id'] = auth()->id();
        Bike::create($validated);
        // Redirigir de vuelta a la ficha personal del usuario
        return redirect()->back()->with('success', 'Bicicleta registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bike $bike)
    {
        $this->authorize('view', $bike);
        return view('bikes.show', compact('bike'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bike $bike)
    {
        $this->authorize('update', $bike);
        return view('bikes.edit', compact('bike'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bike $bike)
    {
        $this->authorize('update', $bike);
        $validated = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'color' => 'required|string|max:255',
        ]);
        $bike->brand = $validated['brand'];
        $bike->model = $validated['model'];
        $bike->color = $validated['color'];
        $bike->save();
        return redirect()->back()->with('success', 'Bicicleta actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bike $bike)
    {
        $this->authorize('delete', $bike);
        try {
            $bike->delete();
            return redirect()->back()->with('success', 'Bicicleta eliminada correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                // Error de integridad referencial
                return redirect()->back()->with('error', 'No se puede eliminar la bicicleta porque tiene registros asociados.');
            }
            throw $e;
        }
    }
}
