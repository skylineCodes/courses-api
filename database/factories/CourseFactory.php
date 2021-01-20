<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Course;
use Faker\Generator as Faker;

$factory->define(Course::class, function (Faker $faker) {
    return [
        'title' => $faker->company,
        'slug' => $faker->slug,
        'description' => $faker->paragraph,
        'price' => $faker->randomNumber($nbDigits = NULL, $strict = false),
        'course_image' => $faker->imageUrl($width = 640, $height = 480),
        'start_date' => $faker->dateTime($max = 'now', $timezone = 'Africa/Lagos'),
        'published' => $faker->randomDigit(1, 2)
    ];
});
