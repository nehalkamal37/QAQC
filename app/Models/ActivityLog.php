<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'project_id',
        'phase_id',
        'sheet_id',
        'qa_item_id',
        'user_id',
        'action_type',
        'old_value',
        'new_value',
        'note',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    // Relations
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function item() {
        return $this->belongsTo(QAItem::class, 'qa_item_id');
    }

    public function sheet() {
        return $this->belongsTo(Sheet::class);
    }

    public function phase() {
        return $this->belongsTo(Phase::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }

    



    /* Icon Mapping */
    public function icon()
    {
        return [
            'status_change'       => 'fas fa-sync',
            'review_added'        => 'fas fa-comment-dots',
            'attachment_uploaded' => 'fas fa-paperclip',
            'item_imported'       => 'fas fa-file-import',
            'qa_assignment'       => 'fas fa-user-check',
            'due_date_changed'    => 'fas fa-calendar',
            'applicable_changed'  => 'fas fa-check-circle',
            'incorporated_changed'=> 'fas fa-layer-group',
            'confirmed_changed'   => 'fas fa-clipboard-check',
        ][$this->action_type] ?? 'fas fa-circle';
    }

    /* Color Mapping */
    public function color()
    {
        return [
            'status_change'       => 'primary',
            'review_added'        => 'success',
            'attachment_uploaded' => 'warning',
            'item_imported'       => 'info',
            'qa_assignment'       => 'dark',
            'due_date_changed'    => 'danger',
            'applicable_changed'  => 'success',
            'incorporated_changed'=> 'info',
            'confirmed_changed'   => 'primary',
        ][$this->action_type] ?? 'secondary';
    }

    /* Extract changed fields for UI */
    public function extractChanges()
    {
        $changes = [];
        if (!$this->new_value) return $changes;

        foreach ($this->new_value as $field => $new) {

            if (in_array($field, ['path','mime','filename'])) {
                continue;
            }

            $old = $this->old_value[$field] ?? '—';

            if ($old != $new) {
                $changes[] = [
                    'field' => ucfirst(str_replace('_',' ', $field)),
                    'from'  => $old ?: '—',
                    'to'    => $new ?: '—'
                ];
            }
        }

        return $changes;
    }

}
