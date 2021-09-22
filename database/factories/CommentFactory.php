<?php

namespace Database\UploadFile\Factories;

use BristolSU\Module\UploadFile\Models\Comment;
use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{

    protected $model = Comment::class;

    public function definition()
    {
        return [
            'file_id' => fn() => \BristolSU\Module\UploadFile\Models\File::factory()->create()->id,
            'comment' => $this->faker->sentence,
            'posted_by' => fn() => \BristolSU\ControlDB\Models\User::factory()->create()->id()
        ];
    }
}
