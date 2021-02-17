<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\AdminApi;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FileSearchController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('admin.file.index');

        $files = File::search($request->input('filter'))
            ->where('module_instance_id', File::moduleInstanceId())
            ->get();
        // Map IDs here for filtering

        $fileIds = $files->pluck('id');

        return $this->paginate(
            File::forModuleInstance()->with(['statuses', 'comments'])->whereIn('id', $fileIds)->get()->map(function(File $file) {
                $activityInstance = $file->activityInstance();
                $file = $file->toArray();
                $file['activity_instance'] = $activityInstance;
                return $file;
            })
        );

/*
 * return own paginator instance
 * search for all files
 * filter based on making sure filter_on is matched, and map to add activity instance data.
 */
    }

}
