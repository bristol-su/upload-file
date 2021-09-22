<?php

namespace BristolSU\Module\UploadFile\Models;

use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\Revision\HasRevisions;
use Database\UploadFile\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes, HasRevisions, HasFactory;

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

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected static function newFactory()
    {
        return new CommentFactory();
    }

}
