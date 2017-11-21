<?php

namespace Spatie\Tags;

use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Collection as DbCollection;

class Tag extends Model implements Sortable
{
    use SortableTrait, HasSlug;

    public $translatable = ['name', 'slug'];

    public $guarded = [];

    public function scopeWithType(Builder $query, string $type = null): Builder
    {
        if (is_null($type)) {
            return $query;
        }

        return $query->where('type', $type)->orderBy('order_column');
    }

    /**
     * @param array|\ArrayAccess $values
     * @param string|null $type
     * @param string|null $locale
     *
     * @return \Spatie\Tags\Tag|static
     */
    public static function findOrCreate($values, string $type = null)
    {
        $tags = collect($values)->map(function ($value) use ($type) {
            if ($value instanceof Tag) {
                return $value;
            }

            return static::findOrCreateFromString($value, $type);
        });

        return is_string($values) ? $tags->first() : $tags;
    }

    public static function getWithType(string $type): DbCollection
    {
        return static::withType($type)->orderBy('order_column')->get();
    }

    public static function findFromString(string $name, string $type = null)
    {
        $query = static::query()
            ->where("slug", $name);

        if($type !== null) {
            $query->where('type', $type);
        }

        return $query->first();
    }

    protected static function findOrCreateFromString(string $name, string $type = null): Tag
    {
        $slug = str_slug($name);
        $tag = static::findFromString($slug, $type);

        if (! $tag) {
            $tag = static::create([
                'slug' => $slug,
                'name' => $name,
                'type' => $type,
            ]);
        }

        return $tag;
    }
}
