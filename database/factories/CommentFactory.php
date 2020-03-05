<?php

$factory->define(\BristolSU\Module\UploadFile\Models\Comment::class, function(\Faker\Generator $faker) {
    return [
        'file_id' => function() {
            return factory(\BristolSU\Module\UploadFile\Models\File::class)->create()->id;
        },
        'comment' => $faker->sentence,
        'posted_by' => function() {
            return factory(\BristolSU\ControlDB\Models\User::class)->create()->id();
        }
    ];
});