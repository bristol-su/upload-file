<?php

namespace BristolSU\Module\UploadFile\Models;

use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\DataPlatform\Contracts\Repositories\User as DataUserRepository;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comments extends Model
{

    use SoftDeletes;

    protected $table = 'uploadfile_comments';

    protected $fillable = [
        'comment',
        'posted_by',
        'file_id',
    ];

    public function getPostedByAttribute($postedById)
    {
        return app()->make(DataUserRepository::class)->getById(
            app()->make(UserRepository::class)->getById($postedById)->data_provider_id
        );
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

}