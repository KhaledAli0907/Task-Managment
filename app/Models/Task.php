<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'status',
        'completed',
        'due_date',
        'assignee_id'
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
