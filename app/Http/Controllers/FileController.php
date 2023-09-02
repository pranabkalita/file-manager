<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FileController extends Controller
{
    public function myFiles(Request $request, string $folder = null)
    {
        if ($folder) {
            $folder = File::query()->where('created_by', auth()->id())->where('path', $folder)->firstOrFail();
        }

        if (!$folder) {
            $folder = $this->getRoot();
        }

        $files = File::query()
            ->where('parent_id', $folder->id)
            ->where('created_by', auth()->id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $files = FileResource::collection($files);

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);

        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render('MyFiles', compact('files', 'folder', 'ancestors', 'folder'));
    }

    public function createFolder(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if (!$parent) {
            $parent = $this->getRoot();
        }

        $file = new File();
        $file->is_folder = 1;
        $file->name = $data['name'];

        $parent->appendNode($file);
    }

    public function store(StoreFileRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        $file_tree = $request->file_tree;

        if (!$parent) {
            $parent = $this->getRoot();
        }

        if ($file_tree) {
            $this->saveFileTree($file_tree, $parent);
        } else {
            foreach ($data['files'] as $file) {
                $this->saveFile($file, $parent);
            }
        }
    }

    private function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', auth()->id())->firstOrFail();
    }

    private function saveFileTree($file_tree, $parent)
    {
        foreach ($file_tree as $name => $file) {
            if (is_array($file)) {
                $folder = new File();
                $folder->is_folder = true;
                $folder->name = $name;

                $parent->appendNode($folder);
                $this->saveFileTree($file, $folder);
            } else {
                $this->saveFile($file, $parent);
            }
        }
    }

    private function saveFile($file, $parent)
    {
        $path = $file->store('/files/' . auth()->id());

        $model = new File();
        $model->storage_path = $path;
        $model->is_folder = false;
        $model->name = $file->getClientOriginalName();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();

        $parent->appendNode($model);
    }
}
