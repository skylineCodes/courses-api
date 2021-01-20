<?php

use Illuminate\Support\Facades\Route;

/**
 * User Authentication - Middleware
 */
Route::post('/register', 'User\UserController@register');
Route::post('/login', 'User\UserController@login');
Route::post('/logout', 'User\UserController@logout');

Route::post('/course/store', 'Course\CourseController@store')->middleware('auth:api');
Route::post('/course/generate', 'Course\CourseController@generateCourse');
Route::get('/courses', 'Course\CourseController@index');
Route::get('/course/{id}', 'Course\CourseController@show');
Route::patch('/course/{course_id}', 'Course\CourseController@courseEnrol')->middleware('auth:api');
Route::get('/courses/enrolled', 'Course\CourseController@enrolledCourses')->middleware('auth:api');
Route::post('/export/csv', 'Course\CourseController@exportCSV');