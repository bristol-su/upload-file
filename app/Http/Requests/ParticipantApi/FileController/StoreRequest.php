<?php

namespace BristolSU\Module\UploadFile\Http\Requests\ParticipantApi\FileController;

use BristolSU\Support\Permissions\Contracts\PermissionTester;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    public function rules()
    {
        return [
            'file' => 'array',
            'file.*' => 'mimes:'.implode(',', settings('allowed_extensions', []))
        ];
    }


    public function authorize()
    {
        return app(PermissionTester::class)->evaluate('uploadfile.file.store');
    }

}