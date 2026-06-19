<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/projects', 'pages::dashboard.projects')->name('projects');
Route::livewire('/projects/{project}', 'pages::dashboard.project-overview')->name('project.overview');
Route::livewire('/projects/{project}/upload', 'pages::dashboard.upload')->name('project.upload');
Route::livewire('/projects/{project}/photos', 'pages::dashboard.photos')->name('project.photos');
Route::livewire('/projects/{project}/faces', 'pages::dashboard.faces')->name('project.faces');
Route::livewire('/projects/{project}/folders', 'pages::dashboard.folders')->name('project.folders');

Route::get('/shared-storage/{path}', function (string $path) {
    return response()->file(Storage::disk('shared')->path($path));
})->where('path', '.*')->name('shared-storage');
