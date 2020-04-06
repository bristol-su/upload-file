<?php

namespace BristolSU\Module\UploadFile\Http\Requests\AdminApi\FileController;

use BristolSU\Support\Permissions\Contracts\PermissionTester;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    public function rules()
    {
        return [
            'file' => 'array',
            'file.*' => 'mimes:'.implode(',', settings('allowed_extensions', [])),
            'activity_instance_id' => 'required'
        ];
    }


    public function authorize()
    {
        return app(PermissionTester::class)->evaluate('uploadfile.admin.file.store');
    }

}