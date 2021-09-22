<?php

namespace Database\UploadFile\Factories;

use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileStatusFactory extends Factory
{

    protected $model = TypeformAuthCode::class;

    public function definition()
    {
        return [
            'file_id' => fn() => \BristolSU\Module\UploadFile\Models\File::factory()->create()->id,
            'created_by' => fn() => \BristolSU\ControlDB\Models\User::factory()->create()->id,
            'status' => $this->faker->randomElement(['Approved', 'Rejected', 'Awaiting Approval'])
        ];
    }
}
