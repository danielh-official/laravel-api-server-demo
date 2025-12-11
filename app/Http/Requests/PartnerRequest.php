<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->tokenCan('edit-partners');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'website' => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
            'level' => 'nullable|string|max:255',
            'image' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'specialties' => 'nullable|array',
            'specialties.*' => 'required|string|max:255',
        ];
    }
}
