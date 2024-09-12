<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Alcance;
use App\Models\Sprint;
use App\Models\Tarea;
use \Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SprintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'n_sprint' => 'required|integer',
            'id_planificacion' => 'required|integer',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'alcance' => ['required', 'string', 'regex:/^[\w\s]+$/'],
            'tareas' => 'required|array',
            'tareas.*.nombre' => ['required', 'string', 'regex:/^[\w\s]+$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos no validos',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();

        try {

            $validated = $validator->validated();
            $sprint = new Sprint;
            $sprint->n_sprint = $validated['n_sprint'];
            $sprint->id_planificacion = $validated['id_planificacion'];
            $sprint->color = $validated['color'];
            $sprint->fecha_inicio = $validated['fecha_inicio'];
            $sprint->fecha_fin = $validated['fecha_fin'];
            $sprint->save();
            $alcance = new Alcance();
            $alcance->nombre = $validated['alcance'];
            $alcance->id_sprint = $sprint->id;
            $sprint->save();
            
            foreach ($validated['tareas'] as $tarea) {
                $tareaN = new Tarea();
                $tareaN->nombre = $tarea['nombre'];
                $tareaN->id_alcance = $alcance->id;
                $tareaN->save();
            }
            DB::commit();
            return response()->json([
                'message' => 'Registro exitoso',
                'sprint' => $sprint,
                'alcance' => $alcance,
                'tareas' => $validated['tareas'],
            ], Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al registrar',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
