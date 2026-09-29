<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\InterventionPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInterventionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'antenna_id' => ['required', 'integer', 'exists:antennas,id'],
            'description' => ['required', 'string', 'min:5', 'max:2000'],
            'technician_identity' => ['required', 'string', 'min:2', 'max:150'],
            'priority' => ['required', 'string', Rule::enum(InterventionPriority::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'antenna_id.required' => "L'identifiant de l'antenne est obligatoire.",
            'antenna_id.exists' => "L'antenne sélectionnée n'existe pas.",
            'description.required' => 'La description de l intervention est obligatoire.',
            'technician_identity.required' => "L'identité du technicien est obligatoire.",
            'priority.required' => 'La priorité est obligatoire.',
            'priority.enum' => 'La priorité doit être LOW, MEDIUM ou HIGH.',
        ];
    }
}
