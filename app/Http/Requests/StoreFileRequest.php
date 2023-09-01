<?php

namespace App\Http\Requests;

use App\Models\File;
use Closure;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends ParentIdBaseRequest
{
    protected function prepareForValidation()
    {
        $paths = array_filter($this->relative_paths ?? [], fn ($f) => $f != null);

        $this->merge([
            'file_paths' => $paths,
            'folder_name' => $this->detectFolderName($paths)
        ]);
    }

    protected function passedValidation()
    {
        $data = $this->validated();

        $this->replace([
            'file_tree' => $this->buildFileTree($this->file_paths, $data['files'])
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'files.*' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$this->folder_name) {
                        $file = File::query()->where('name', $value->getClientOriginalName())
                            ->where('created_by', auth()->id())
                            ->where('parent_id', $this->parent_id)
                            ->exists();

                        if ($file) {
                            $fail('File "' . $value->getClientOriginalName() . '" already exists.');
                        }
                    }
                }
            ],
            'folder_name' => [
                'nullable',
                'string',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($value) {
                        $file = File::query()->where('name', $value)
                            ->where('created_by', auth()->id())
                            ->where('parent_id', $this->parent_id)
                            ->exists();

                        if ($file) {
                            $fail('Folder "' . $value . '" already exists.');
                        }
                    }
                }
            ],
        ]);
    }

    private function detectFolderName($paths)
    {
        if (!$paths) {
            return null;
        }

        $parts = explode("/", $paths[0]);

        return $parts[0];
    }

    private function buildFileTree($filePaths, $files)
    {
        $filePaths = array_slice($filePaths, 0, count($files));
        $filePaths = array_filter($filePaths, fn ($f) => $f != null);

        // $tree = [];
        // $currentNode = [];
        // foreach ($filePaths as $ind => $filePath) {
        //     $parts = explode('/', $filePath);

        //     $currentNode = &$tree;
        //     foreach ($parts as $i => $part) {
        //         if (!isset($currentNode[$part])) {
        //             $currentNode[$part] = [];
        //         }

        //         if ($i === count($files) - 1) {
        //             $currentNode[$part] = $files[$ind];
        //         } else {
        //             $currentNode = &$currentNode[$part];
        //         }
        //     }
        // }

        $tree = [];
        $currentNode = [];
        foreach ($filePaths as $ind => $filePath) {
            $parts = explode('/', $filePath);
            $currentNode = &$tree;
            foreach ($parts as $i => $part) {
                if (!isset($currentNode[$part])) {
                    $currentNode[$part] = [];
                }
                $currentNode = &$currentNode[$part];
            }
            $currentNode = $files[$ind];
        }

        return $tree;
    }
}
