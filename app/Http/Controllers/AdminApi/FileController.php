<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\UploadFile\Events\DocumentDeleted;
use BristolSU\Module\UploadFile\Events\DocumentUpdated;
use BristolSU\Module\UploadFile\Events\DocumentUploaded;
use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Http\Requests\AdminApi\FileController\StoreRequest;
use BristolSU\Module\UploadFile\Models\File;
use BristolSU\Support\Activity\Activity;
use BristolSU\Support\ActivityInstance\Contracts\ActivityInstanceResolver;
use BristolSU\Support\Authentication\Contracts\Authentication;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FileController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('admin.file.index');

        return File::forModuleInstance()->with(['statuses', 'comments'])->paginate(
            $request->input('per_page', 5), ['*'], 'page', $request->input('page', 1)
        );
    }

    public function show(Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('admin.file.index');

        return $file->load(['statuses', 'comments']);
    }

    public function store(StoreRequest $request, Authentication $authentication)
    {
        $fileMetadata = collect();

        foreach (Arr::wrap($request->file('file')) as $file) {

            $path = $file->store('uploadfile');

            $fileMetadata->push($tempFileMeta = File::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'filename' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'path' => $path,
                'size' => $file->getSize(),
                'uploaded_by' => $authentication->getUser()->id(),
                'activity_instance_id' => $request->input('activity_instance_id'),
                'tags' => settings('new_tags', [])
            ])->load(['statuses', 'comments']));

            event(new DocumentUploaded($tempFileMeta));
        }

        return $fileMetadata;
    }

    public function destroy(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('admin.file.destroy');

        $file->delete();
        $file->refresh();

        event(new DocumentDeleted($file));

        return $file->refresh();
    }

    public function update(Request $request, Activity $activity, ModuleInstance $moduleInstance, File $file)
    {
        $this->authorize('admin.file.update');

        $file->title = $request->input('title', $file->title);
        $file->description = $request->input('description', $file->description);
        $file->activity_instance_id = $request->input('activity_instance_id', $file->activity_instance_id);

        $file->save();

        event(new DocumentUpdated($file));

        return $file;
    }

}
