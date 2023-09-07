<?php

namespace App\Http\Controllers;

use App\Models\File;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\FileResource;
use App\Http\Requests\StoreFileRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\FilesActionRequest;
use App\Http\Requests\RestoreTrashRequest;
use App\Http\Requests\StoreFolderRequest;
use ZipArchive;

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

    public function destroy(FilesActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if ($data['all']) {
            $children = $parent->children;

            foreach ($children as $child) {
                $child->moveToTrash();
            }
        } else {
            foreach ($data['ids'] as $id) {
                $file = File::find($id);
                $file->moveToTrash();
            }
        }

        return redirect()->back();
    }

    public function download(FilesActionRequest $request)
    {
        $validated = $request->validated();
        $parent = $request->parent;

        $all = $validated['all'] ?? false;
        $ids = $validated['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to download.'
            ];
        }

        if ($all) {
            $url = $this->createZip($parent->children);
            $filename = $parent->name . '.zip';
        } else {
            if (count($ids) == 1) {
                $file = File::find($ids[0]);
                if ($file->is_folder) {
                    if ($file->children->count() == 0) {
                        return [
                            'message' => 'The folder is empty.'
                        ];
                    }

                    $url = $this->createZip($file->children);
                    $filename = $file->name . '.zip';
                } else {
                    $destination = 'public/' . pathinfo($file->storage_path, PATHINFO_BASENAME);
                    Storage::copy($file->storage_path, $destination);

                    $url = asset(Storage::url($destination));
                    $filename = $file->name;
                }
            } else {
                $files = File::query()->whereIn('id', $ids)->get();
                $url = $this->createZip($files);
                $filename = $parent->name . '.zip';
            }
        }

        return [
            'url' => $url,
            'filename' => $filename
        ];
    }

    public function trash(Request $request)
    {
        $files = File::onlyTrashed()
            ->where('created_by', auth()->id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render('Trash', compact('files'));
    }

    public function restore(RestoreTrashRequest $request)
    {
        $data = $request->validated();

        if ($data['all']) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->restore();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $children = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($children as $child) {
                $child->restore();
            }
        }
    }

    public function deleteForever(RestoreTrashRequest $request)
    {
        $data = $request->validated();

        if ($data['all']) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $children = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($children as $child) {
                $child->deleteFoever();
            }
        }
    }

    private function createZip($files): string
    {
        $zipPath = 'zip/' . Str::random() . '.zip';
        $publicPath = "public/$zipPath";

        if (!is_dir(dirname($publicPath))) {
            Storage::makeDirectory(dirname($publicPath));
        }

        $zipFile = Storage::path($publicPath);

        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $this->addFilesToZip($zip, $files);
        }

        $zip->close();

        return asset(Storage::url($zipPath));
    }

    private function addFilesToZip(ZipArchive $zip, $files, $ancestors = '')
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . '/');
            } else {
                $zip->addFile(Storage::path($file->storage_path), $ancestors . $file->name);
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
