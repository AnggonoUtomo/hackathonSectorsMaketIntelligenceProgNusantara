<?php

namespace App\Modules\Comparison\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ComparisonSnapshot extends Model
{
    use HasUlids;

    protected $table = 'comparison_snapshots';

    protected $fillable = [
        'user_id',
        'title',
        'symbols',
        'payload',
        'version',
        'created_from_snapshot_id',
    ];

    protected function casts(): array
    {
        return [
            'symbols' => 'array',
            'payload' => 'array',
            'version' => 'integer',
        ];
    }
}
