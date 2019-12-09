<?php

namespace BristolSU\Module\UploadFile\Http\Requests\ParticipantApi\FileController;

use BristolSU\Support\Permissions\Contracts\PermissionTester;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    public function rules()
    {
        return [];
    }

    public function validator(Factory $factory)
    {
        $v = $factory->make($this->validationData(), $this->rules());
        $v->sometimes('file', 'mimes:'.implode(',', settings('allowed_extensions', [])), function($input) {
            return !is_array($input);
        });
        $v->sometimes('file.*', 'mimes:'.implode(',', settings('allowed_extensions', [])), function($input) {
            return is_array($input);
        });
        return $v;
    }

    public function authorize()
    {
        return app(PermissionTester::class)->evaluate('uploadfile.file.store');
    }

}