<?php

use App\Models\Photo;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/projects', 'pages::dashboard.projects')->name('projects');
Route::livewire('/projects/{project}', 'pages::dashboard.project-overview')->name('project.overview');
Route::livewire('/projects/{project}/upload', 'pages::dashboard.upload')->name('project.upload');
Route::livewire('/projects/{project}/photos', 'pages::dashboard.photos')->name('project.photos');
Route::livewire('/projects/{project}/faces', 'pages::dashboard.faces')->name('project.faces');
Route::livewire('/projects/{project}/clusters', 'pages::dashboard.clusters')->name('project.clusters');
Route::livewire('/projects/{project}/people', 'pages::dashboard.people')->name('project.people');
Route::livewire('/projects/{project}/folders', 'pages::dashboard.folders')->name('project.folders');

Route::get('/projects/{project}/folders/export', function (Project $project) {
    $personIds = request()->input('person_ids', []);
    $excludedIds = request()->input('excluded_ids', []);

    if (empty($personIds)) {
        abort(400, 'No people selected.');
    }

    $photos = Photo::where('project_id', $project->id);

    foreach ($personIds as $personId) {
        $photos->whereHas('faces', fn ($q) => $q->where('person_id', $personId));
    }

    if (! empty($excludedIds)) {
        $photos->whereNotIn('id', $excludedIds);
    }

    $photos = $photos->latest()->get();

    if ($photos->isEmpty()) {
        abort(404, 'No photos found for the selected people.');
    }

    $zip = new ZipArchive;
    $tempFile = tempnam(sys_get_temp_dir(), 'export_');
    $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($photos as $photo) {
        $filePath = Storage::disk('shared')->path($photo->path);

        if (file_exists($filePath)) {
            $zip->addFile($filePath, basename($photo->path));
        }
    }

    $zip->close();

    return response()->download($tempFile, Str::slug($project->name).'-export.zip')->deleteFileAfterSend();
})->name('project.folders.export');

Route::get('/shared-storage/{path}', function (string $path) {
    return response()->file(Storage::disk('shared')->path($path));
})->where('path', '.*')->name('shared-storage');
