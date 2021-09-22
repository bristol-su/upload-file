<?php

namespace Database\UploadFile\Factories;

use BristolSU\Module\UploadFile\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{

    protected $model = File::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->sentence,
            'filename' => $this->faker->word,
            'mime' => $this->faker->mimeType,
            'path' => \Illuminate\Support\Str::random(40),
            'size' => $this->faker->numberBetween(500, 99999999),
            'uploaded_by' => fn() => \BristolSU\ControlDB\Models\User::factory()->create()->id(),
            'module_instance_id' => fn() => \BristolSU\Support\ModuleInstance\ModuleInstance::factory()->create()->id,
            'activity_instance_id' => fn() => \BristolSU\Support\ActivityInstance\ActivityInstance::factory()->create()->id,
            'tags' => $this->faker->randomElements(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'], 4)
        ];
    }
}
