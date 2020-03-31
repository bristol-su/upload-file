<?php

namespace BristolSU\Module\UploadFile\Models;

use BristolSU\Support\ActivityInstance\Contracts\ActivityInstanceRepository;
use BristolSU\Support\Authentication\HasResource;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\ModuleInstance\Contracts\ModuleInstanceRepository;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class File extends Model
{
    use SoftDeletes, HasResource;

    protected $table = 'uploadfile_files';
    
    protected $appends = ['status'];
    
    protected $fillable = [
        'title',
        'description',
        'filename',
        'mime',
        'path',
        'size',
        'uploaded_by',
        'module_instance_id',
        'activity_instance_id',
    ];

    public function getUploadedByAttribute($uploadedById)
    {
        return app()->make(UserRepository::class)->getById($uploadedById);
    }

    /**
     * @return ModuleInstance
     */
    public function moduleInstance()
    {
        return app(ModuleInstanceRepository::class)->getById($this->module_instance_id);
    }

    public function activityInstance()
    {
        return app(ActivityInstanceRepository::class)->getById($this->activity_instance_id);
    }

    public function statuses()
    {
        return $this->hasMany(FileStatus::class);
    }

    public function getStatusAttribute()
    {
        if($this->statuses()->count() > 0) {
            return $this->statuses()->latest('created_at')->first()->status;
        }
        
        $statuses = Config::get('uploadfile.statuses');
        if(!is_array($statuses) || count($statuses) === 0) {
            $default = 'Awaiting Approval';
        } else {
            $default = $statuses[0];
        }
        
        return $this->moduleInstance()->setting('initial_status', $default);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}