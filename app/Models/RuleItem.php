<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_section_id',
        'item_number',
        'title',
        'content',
        'is_highlighted',
        'highlight_type',
        'display_order',
    ];

    protected $casts = [
        'is_highlighted' => 'boolean',
        'display_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(RuleSection::class, 'rule_section_id');
    }
}
