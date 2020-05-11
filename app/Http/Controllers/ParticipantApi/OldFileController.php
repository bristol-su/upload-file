<?php

namespace BristolSU\Module\UploadFile\Http\Controllers\ParticipantApi;

use BristolSU\Module\UploadFile\Http\Controllers\Controller;
use BristolSU\Module\UploadFile\Models\File;

class OldFileController extends Controller
{

    public function index()
    {
        $files = collect();
        foreach(settings('tags_to_merge', []) as $tag) {
            foreach(File::withTag($tag)->get() as $file) {
                $files->push($file);
            }
        }
        return $files->unique(function(File $file) {
            return $file->id;
        })->map(function(File $file) {
            return $file->load(['statuses', 'comments']);
        })->values();
    }

}