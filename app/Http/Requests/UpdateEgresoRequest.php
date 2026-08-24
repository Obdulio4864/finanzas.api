<?php

namespace App\Http\Requests;

use App\Models\Egreso;

class UpdateEgresoRequest extends StoreEgresoRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('subcategoria_id') || $this->filled('categoria_id')) {
            return;
        }

        $categoriaId = Egreso::query()
            ->where('user_id', $this->user()->id)
            ->whereKey($this->route('egreso'))
            ->value('categoria_id');

        if ($categoriaId !== null) {
            $this->merge(['categoria_id' => $categoriaId]);
        }
    }

    public function rules(): array
    {
        $rules = parent::rules();

        foreach (['categoria_id', 'fecha', 'descripcion', 'monto'] as $campo) {
            $rules[$campo][0] = 'sometimes';
        }

        return $rules;
    }
}
