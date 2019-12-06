<?php

namespace BristolSU\Module\UploadFile\Models;

use BristolSU\Support\Authentication\HasResource;
use BristolSU\Support\Control\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\DataPlatform\Contracts\Repositories\User as DataUserRepository;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes, HasResource;

    protected $table = 'uploadfile_files';
    
    protected $appends = ['status'];
    
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
        return app()->make(DataUserRepository::class)->getById(
            app()->make(UserRepository::class)->getById($uploadedById)->dataPlatformId()
        );
    }
    
    public function moduleInstance()
    {
        return $this->belongsTo(ModuleInstance::class);
    }

    public function statuses()
    {
        return $this->hasMany(FileStatus::class);
    }

    public function getStatusAttribute()
    {
        if($this->statuses()->count() > 0) {
            return $this->statuses->last()->status;
        }
        return settings('initial_status');
    }

}