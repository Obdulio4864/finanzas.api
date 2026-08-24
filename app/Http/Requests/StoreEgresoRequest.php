<?php

namespace App\Http\Requests;

use App\Models\Subcategoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEgresoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => [
                'required',
                'integer',
                Rule::exists('categorias', 'id')
                    ->where('tipo', 'egreso')
                    ->where(fn ($query) => $query
                        ->where('user_id', $this->user()->id)
                        ->orWhereNull('user_id')),
            ],
            'subcategoria_id' => ['nullable', 'integer'],
            'fecha' => ['required', 'date'],
            'descripcion' => ['required', 'string', 'max:150'],
            'monto' => ['required', 'decimal:0,2', 'min:0'],
            'notas' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('subcategoria_id') || ! $this->filled('categoria_id')) {
                return;
            }

            $esValida = Subcategoria::query()
                ->whereKey($this->integer('subcategoria_id'))
                ->where('categoria_id', $this->integer('categoria_id'))
                ->whereHas('categoria', fn (Builder $query) => $query
                    ->where('tipo', 'egreso')
                    ->where(fn (Builder $query) => $query
                        ->where('user_id', $this->user()->id)
                        ->orWhereNull('user_id')))
                ->exists();

            if (! $esValida) {
                $validator->errors()->add('subcategoria_id', 'La subcategoría no pertenece a una categoría de egreso disponible.');
            }
        });
    }
}
