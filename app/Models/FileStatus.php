<?php


namespace BristolSU\Module\UploadFile\Models;


use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\DataPlatform\Contracts\Repositories\User as DataUserRepository;
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
        return app()->make(DataUserRepository::class)->getById(
            app()->make(UserRepository::class)->getById($createdById)->dataPlatformId()
        );
    }
    
    
    
}