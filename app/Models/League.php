<?php
namespace App\Models;

use Database\Factories\LeagueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'logo_path', 'description', 'stats', 'start_date', 'end_date', 'type'])]
class League extends Model
{
    /** @use HasFactory<LeagueFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function groupCards(): BelongsToMany
    {
        return $this->belongsToMany(GroupCard::class);
    }
}
