<?php

namespace BristolSU\Module\UploadFile\Models;

use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
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
        return app()->make(UserRepository::class)->getById($postedById)->data();
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

}