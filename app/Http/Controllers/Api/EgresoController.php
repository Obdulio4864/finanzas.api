<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexEgresoRequest;
use App\Http\Requests\StoreEgresoRequest;
use App\Http\Requests\UpdateEgresoRequest;
use App\Models\Egreso;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EgresoController extends Controller
{
    public function index(IndexEgresoRequest $request): JsonResponse
    {
        $egresos = $this->egresosDelUsuario($request)
            ->when(
                $request->filled('anio'),
                fn (Builder $query) => $query->whereYear('fecha', $request->integer('anio')),
            )
            ->when(
                $request->filled('mes'),
                fn (Builder $query) => $query->whereMonth('fecha', $request->integer('mes')),
            )
            ->orderByDesc('fecha')
            ->get();

        return response()->json(['data' => $egresos]);
    }

    public function store(StoreEgresoRequest $request): JsonResponse
    {
        $egreso = Egreso::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'data' => $this->egresosDelUsuario($request)->findOrFail($egreso->id),
        ], Response::HTTP_CREATED);
    }

    public function show(IndexEgresoRequest $request, int $egreso): JsonResponse
    {
        return response()->json([
            'data' => $this->egresosDelUsuario($request)->findOrFail($egreso),
        ]);
    }

    public function update(UpdateEgresoRequest $request, int $egreso): JsonResponse
    {
        $egreso = $this->egresosDelUsuario($request)->findOrFail($egreso);
        $egreso->update($request->validated());

        return response()->json([
            'data' => $this->egresosDelUsuario($request)->findOrFail($egreso->id),
        ]);
    }

    public function destroy(IndexEgresoRequest $request, int $egreso): Response
    {
        $this->egresosDelUsuario($request)->findOrFail($egreso)->delete();

        return response()->noContent();
    }

    private function egresosDelUsuario(Request $request): Builder
    {
        $userId = $request->user()->id;

        return Egreso::query()
            ->where('user_id', $userId)
            ->with([
                'categoria' => fn (Builder $query) => $query
                    ->where('tipo', 'egreso')
                    ->where(fn (Builder $query) => $query
                        ->where('user_id', $userId)
                        ->orWhereNull('user_id')),
                'subcategoria' => fn (Builder $query) => $query
                    ->whereHas('categoria', fn (Builder $query) => $query
                        ->where('tipo', 'egreso')
                        ->where(fn (Builder $query) => $query
                            ->where('user_id', $userId)
                            ->orWhereNull('user_id'))),
            ]);
    }
}
