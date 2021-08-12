<?php

namespace BristolSU\Module\UploadFile\Models;

use BristolSU\Support\ActivityInstance\ActivityInstance;
use BristolSU\Support\ActivityInstance\Contracts\ActivityInstanceRepository;
use BristolSU\Support\Authentication\HasResource;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\Support\ModuleInstance\Contracts\ModuleInstanceRepository;
use BristolSU\Support\ModuleInstance\ModuleInstance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
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
        'tags'
    ];

    protected $casts = [
        'tags' => 'array'
    ];

    public function getUploadedByAttribute($uploadedById)
    {
        return app()->make(UserRepository::class)->getById($uploadedById);
    }

    public function scopeWithTag(Builder $query, string $tag)
    {
        $activityInstanceRepository = app(ActivityInstanceRepository::class);
        $activityInstance = $activityInstanceRepository->getById(static::activityInstanceId());
        $activityInstanceIds = $activityInstanceRepository
            ->allForResource($activityInstance->resource_type, $activityInstance->resource_id)
            ->map(function(ActivityInstance $activityInstance) {
                return $activityInstance->id;
            });
        return $query->whereIn('activity_instance_id', $activityInstanceIds->toArray())
            ->where('tags', 'LIKE', '%"' . $tag . '"%');
    }

    /**
     * @return ModuleInstance
     */
    public function moduleInstance()
    {
        return app(ModuleInstanceRepository::class)->getById($this->module_instance_id);
    }

    /**
     * @return ActivityInstance
     */
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
