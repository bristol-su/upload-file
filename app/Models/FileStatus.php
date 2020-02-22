<?php


namespace BristolSU\Module\UploadFile\Models;


use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use Illuminate\Database\Eloquent\Model;

class FileStatus extends Model
{

    protected $table = 'uploadfile_file_statuses';

    protected $guarded = [];
    
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function getCreatedByAttribute($createdById)
    {
        return app()->make(UserRepository::class)->getById($createdById)->data();
    }
    
    
    
}