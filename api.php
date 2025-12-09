<?php

Route::get('/phases-by-project/{project}', function ($projectId) {
    return \App\Models\Phase::where('project_id', $projectId)->get(['id', 'type']);
});
