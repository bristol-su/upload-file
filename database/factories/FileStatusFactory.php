<?php

$factory->define(\BristolSU\Module\UploadFile\Models\FileStatus::class, function(\Faker\Generator $faker) {
    return [
        'file_id' => function() {
            return factory(\BristolSU\Module\UploadFile\Models\File::class)->create()->id;
        },
        'created_by' => function() {
            return factory(\BristolSU\ControlDB\Models\User::class)->create()->id;
        },
        'status' => $faker->randomElement(['Approved', 'Rejected', 'Awaiting Approval'])
    ];
});