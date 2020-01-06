<?php

namespace BristolSU\Module\UploadFile\Http\Requests\ParticipantApi\FileController;

use BristolSU\Support\Permissions\Contracts\PermissionTester;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    public function rules()
    {
        return [
            'file.*' => 'mimes:'.implode(',', settings('allowed_extensions', []))
        ];
    }

    public function validator(Factory $factory)
    {
        return $factory->make($this->validationData(), $this->rules());
    }

    public function authorize()
    {
        return app(PermissionTester::class)->evaluate('uploadfile.file.store');
    }

}