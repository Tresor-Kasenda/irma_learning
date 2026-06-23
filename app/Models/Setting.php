<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $guarded = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        $record = self::query()->where('key', $key)->first();

        return $record ? $record->value : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
