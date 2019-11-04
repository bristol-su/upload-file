<?php

namespace BristolSU\Module\UploadFile\Models;

use BristolSU\Support\Authentication\HasResource;
use BristolSU\Support\Control\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes, HasResource;

    protected $table = 'uploadfile_files';
    
    protected $fillable = [
        'title',
        'filename',
        'mime',
        'path',
        'size',
        'uploaded_by',
        'module_instance_id',
        'resource_type',
        'resource_id'
    ];

    public function getUploadedByAttribute($uploadedById)
    {
        return app()->make(UserRepository::class)->getById($uploadedById);
    }
    
    public function moduleInstance()
    {
        return $this->belongsTo(ModuleInstance::class);
    }

}