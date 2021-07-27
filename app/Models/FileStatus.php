<?php


namespace BristolSU\Module\UploadFile\Models;


use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\Revision\HasRevisions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileStatus extends Model
{
    use SoftDeletes, HasRevisions;

    protected $table = 'uploadfile_file_statuses';

    protected $fillable = [
        'file_id', 'status', 'created_by'
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function getCreatedByAttribute($createdById)
    {
        return app()->make(UserRepository::class)->getById($createdById);
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

}
