<?php

namespace App\Http\Requests;

use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends ParentIdBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'name' => [
                'required',
                Rule::unique(File::class, 'name')
                    ->where('created_by', auth()->id())
                    ->when($this->parent, function ($query) {
                        return $query->where('parent_id', $this->parent->id);
                    })
            ]
        ]);
    }

    public function messages()
    {
        return [
            'name.unique' => 'Folder ":input" already exists.'
        ];
    }
}
