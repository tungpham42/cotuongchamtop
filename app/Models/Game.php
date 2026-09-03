<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $views
 * @property string $initial_fen
 * @property array|null $moves
 */
class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'views',
        'initial_fen',
        'moves',
    ];

    protected $casts = [
        'moves' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Tự động sinh slug từ title khi tạo mới (nếu chưa có slug)
        static::creating(function (Game $game) {
            if (empty($game->slug)) {
                $game->slug = static::generateUniqueSlug($game->title);
            }
        });

        // Nếu slug bị để trống khi update, sinh lại từ title hiện tại
        static::updating(function (Game $game) {
            if (empty($game->slug)) {
                $game->slug = static::generateUniqueSlug($game->title, $game->id);
            }
        });
    }

    /**
     * Sinh slug duy nhất từ title, tự thêm hậu tố số nếu bị trùng.
     */
    public static function generateUniqueSlug(string $title, $ignoreId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'van-co';
        $slug = $baseSlug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }

    // Tự động tìm model bằng cột 'slug' thay vì 'id' trên URL
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
