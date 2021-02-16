<?php

namespace BristolSU\Module\UploadFile\Models;

use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\Revision\HasRevisions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes, HasRevisions;

    protected $table = 'uploadfile_comments';

    protected $fillable = [
        'comment',
        'posted_by',
        'file_id',
    ];

    public function getPostedByAttribute($postedById)
    {
        return app()->make(UserRepository::class)->getById($postedById);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

}
