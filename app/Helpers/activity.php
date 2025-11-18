<?php

use App\Models\ActivityLog;

function logActivity($data)
{
    ActivityLog::create([
        'project_id'  => $data['project_id'] ?? null,
        'phase_id'    => $data['phase_id'] ?? null,
        'sheet_id'    => $data['sheet_id'] ?? null,
        'qa_item_id'  => $data['qa_item_id'] ?? null,
        'user_id'     => auth()->id(),
        'action_type' => $data['action_type'],
        'old_value'   => $data['old'] ?? null,
    'new_value'   => $data['new'] ?? null,
        'note'        => $data['note'] ?? null,
    ]);
}
