<?php

namespace Monstrex\AveSite\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Monstrex\Ave\Media\ImageProcessor;
use Monstrex\AveSite\Contracts\DataContract;
use Monstrex\AveSite\Exceptions\AveSiteException;
use Schema;

class DataService implements DataContract
{
    protected ModelResolver $modelResolver;

    public function __construct(ModelResolver $modelResolver)
    {
        $this->modelResolver = $modelResolver;
    }
    /**
     * Find model record by SLUG or ID
     *
     * @param string|int $alias Slug or ID
     * @param string|null $modelSlug Model table name
     * @param bool $fail Throw 404 if not found
     * @return mixed
     */
    public function findFirst($alias, string $modelSlug = null, bool $fail = true)
    {
        if (is_int($alias)) {
            return $this->where('id', $alias, $modelSlug, $fail);
        }

        return $this->where('slug', $alias, $modelSlug, $fail);
    }

    /**
     * Find (first) model record by Field and Value
     *
     * @param string $field
     * @param string $value
     * @param string|null $modelSlug
     * @param bool $fail
     * @return mixed
     */
    public function where(string $field, string $value, string $modelSlug = null, bool $fail = true)
    {
        $table = $modelSlug ?? config('ave-site.default_model_table', 'ave_site_pages');
        $model = $this->getModel($table);

        $data = $model
            ? $model::where($field, $value)->first()
            : DB::table($table)->where($field, $value)->first();

        if ($data && !$this->recordIsActive($data)) {
            $data = null;
        }

        if (!$data && $fail) {
            $this->throwNotFound();
        }

        return $data;
    }

    /**
     * Find records using given field and value (only with status = 1)
     *
     * @param string $modelSlug
     * @param string $field
     * @param mixed $value
     * @param string $order
     * @param string $direction
     * @return mixed
     */
    public function findByField(string $modelSlug, string $field, $value, string $order = 'order', string $direction = 'ASC')
    {
        $table = $modelSlug ?? config('ave-site.default_model_table', 'ave_site_pages');
        $model = $this->getModel($table);
        $statusField = $this->statusField();
        $activeValues = $this->activeValues();

        if ($model) {
            $query = $model::where($field, $value);
            if ($statusField) {
                $query->whereIn($statusField, $activeValues);
            }

            return $query->orderBy($order, $direction)->get();
        }

        $query = DB::table($table)->where($field, $value);
        if ($statusField) {
            $query->whereIn($statusField, $activeValues);
        }

        return $query->orderBy($order, $direction)->get();
    }

    /**
     * Load multiple DataSources
     *
     * @param array $datasources Associative array [key => config]
     * @return array [key => Collection]
     */
    public function getDataSources(array $datasources): array
    {
        if (empty($datasources)) {
            return [];
        }

        $data = [];

        foreach ($datasources as $key => $config) {
            if (is_array($config) && isset($config['model'])) {
                $data[$key] = $this->getDataSource($config);
            } else {
                // Simple variable (not model)
                $data[$key] = $config;
            }
        }

        return $data;
    }

    /**
     * Load one DataSource using JSON config
     *
     * @param array $config Configuration: model, where, order, limit, with, random
     * @return array
     */
    protected function getDataSource(array $config): array
    {
        $modelClass = $config['model'];

        if (!class_exists($modelClass)) {
            return [];
        }

        $query = $modelClass::query();

        // Where (multiple conditions)
        if (isset($config['where'])) {
            foreach ($config['where'] as $field => $value) {
                $query->where($field, $value);
            }
        }

        // Order By
        if (isset($config['order'])) {
            $field = $config['order']['field'] ?? 'id';
            $direction = $config['order']['direction'] ?? 'ASC';
            $query->orderBy($field, $direction);
        }

        // Random
        if (isset($config['random']) && $config['random']) {
            $query->inRandomOrder();
        }

        // With Relations
        if (isset($config['with'])) {
            $query->with($config['with']);
        }

        // Get Collection
        $collection = $query->get();

        // Limit (after fetching, for Voyager Site compatibility)
        if (isset($config['limit'])) {
            $collection = $collection->take($config['limit']);
        }

        // Use ModelResolver to process each record with all Media/FieldSet/JSON fields
        $records = $collection->map(function ($record) {
            return $this->modelResolver->resolveModel($record);
        })->toArray();

        return $records;
    }

    /**
     * Get Model using given model slug (table name)
     *
     * @param string|null $modelSlug
     * @return object|null
     */
    protected function getModel(?string $modelSlug): ?object
    {
        $class = $this->resolveModelClass($modelSlug);

        if ($class && class_exists($class)) {
            return app($class);
        }

        return null;
    }

    protected function statusField(): ?string
    {
        if (!$this->isStatusCheckEnabled()) {
            return null;
        }

        $field = config('ave-site.status.field', 'status');

        return $field ?: null;
    }

    /**
     * @return array<int,mixed>
     */
    protected function activeValues(): array
    {
        return array_map(
            fn ($value) => $this->normalizeStatusValue($value),
            Arr::wrap(config('ave-site.status.active_value', 1))
        );
    }

    protected function isStatusCheckEnabled(): bool
    {
        return (bool) (config('ave-site.status.enabled', true));
    }

    protected function recordIsActive(mixed $record): bool
    {
        $field = $this->statusField();
        if (!$field) {
            return true;
        }

        $value = $this->normalizeStatusValue(data_get($record, $field));

        return in_array($value, $this->activeValues(), true);
    }

    protected function throwNotFound(): void
    {
        if (config('ave-site.use_legacy_error_handler', false)) {
            abort(404);
        }

        throw new AveSiteException(__('ave-site::errors.page_not_found'), 404);
    }

    protected function normalizeStatusValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_null($value)) {
            return '';
        }

        return (string) $value;
    }

    protected function resolveModelClass(?string $modelSlug): ?string
    {
        if ($modelSlug && class_exists($modelSlug)) {
            return $modelSlug;
        }

        $models = config('ave-site.models', []);
        $tableMap = config('ave-site.table_model_map', []);

        if (!$modelSlug) {
            $defaultTable = config('ave-site.default_model_table', 'ave_site_pages');
            $key = $tableMap[$defaultTable] ?? array_key_first($models);

            return $key ? ($models[$key] ?? null) : null;
        }

        if (isset($models[$modelSlug])) {
            return $models[$modelSlug];
        }

        if (isset($tableMap[$modelSlug])) {
            $key = $tableMap[$modelSlug];

            return $models[$key] ?? null;
        }

        return null;
    }

    /**
     * Get Tree Menu Items
     *
     * @param string|null $modelSlug
     * @param array|null $parent
     * @return array|null
     */
    public function getMenu(string $modelSlug = null, array $parent = null)
    {
        $table = $modelSlug ?? config('ave-site.default_model_table', 'ave_site_pages');
        $model = $this->getModel($table);

        if (!$model) {
            return null;
        }

        // Check if it's tree model
        if (!Schema::hasColumn($model->getTable(), 'parent_id')) {
            return null;
        }

        $menu_items = $this->flatToTree($model::all()->toArray());

        if ($parent) {
            $menu_items = [$this->getMenuChildren($menu_items, $parent)];
        }

        return $menu_items;
    }

    /**
     * Get Children Tree Menu Items
     *
     * @param array $items
     * @param array $parent
     * @return array|null
     */
    protected function getMenuChildren($items, $parent)
    {
        foreach ($items as $key => $item) {
            if ($item[$parent['field']] === $parent['value']) {
                return $item;
            }

            if (isset($item['children'])) {
                if ($result = $this->getMenuChildren($item['children'], $parent)) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Convert flat array to tree structure
     *
     * @param array $array
     * @param int|null $parent_id
     * @return array
     */
    protected function flatToTree(array $array, $parent_id = null): array
    {
        $tree = [];

        foreach ($array as $item) {
            if ($item['parent_id'] == $parent_id) {
                $children = $this->flatToTree($array, $item['id']);

                if ($children) {
                    $item['children'] = $children;
                }

                $tree[] = $item;
            }
        }

        return $tree;
    }

    /**
     * If a requested Image doesn't exist - it will be created
     * Returns relative image URL to a new (or old) image
     *
     * @param string $image_url Full (with HOST) or relative URL
     * @param int|null $width New width of image
     * @param int|null $height New height of image
     * @param string|null $format New image format ('webp','png')
     * @param int|null $quality Image quality
     * @return string
     */
    public function getImageOrCreate(string $image_url, int $width = null, int $height = null, string $format = null, int $quality = null): string
    {
        // Windows path fix
        $image_url = Str::replaceFirst('\\', '/', $image_url);

        $origin_url = $image_url;

        // Convert URL to storage path
        // Input can be:
        // - /storage/files/media/... (relative URL with /storage prefix)
        // - https://host.com/storage/files/media/... (full URL)
        // - files/media/... (already a storage path)

        // Remove host if present
        $parsed = parse_url($image_url);
        if (isset($parsed['host'])) {
            $image_url = $parsed['path'] ?? '';
        }

        // Remove /storage prefix (public disk URL prefix)
        if (Str::startsWith($image_url, '/storage/')) {
            $image_url = Str::replaceFirst('/storage/', '', $image_url);
        } elseif (Str::startsWith($image_url, '/storage')) {
            $image_url = Str::replaceFirst('/storage', '', $image_url);
        }

        // Remove leading slash
        $image_url = ltrim($image_url, '/');

        $path_info = pathinfo($image_url);

        if (!isset($path_info['dirname'])
            || !isset($path_info['basename'])
            || !isset($path_info['extension'])
            || !isset($path_info['filename'])
        ) {
            return $image_url;
        }

        $format = $format ?? $path_info['extension'];
        $quality = $quality ?? 80;

        $sizes = $width || $height ? '-' . $width . 'x' . $height : '';
        $cacheDir = $width || $height ? 'cache/' : '';

        $target_path_full = $path_info['dirname'] . '/'
            . $cacheDir
            . $path_info['filename']
            . $sizes
            . '.' . $format;

        // Use public disk for storage operations (files are in storage/app/public)
        $disk = Storage::disk('public');

        if (!$disk->exists($image_url)) {
            return $origin_url;
        }

        if (!$disk->exists($target_path_full)) {
            try {
                // Use Ave ImageProcessor
                $fullPath = $disk->path($image_url);
                $processor = new ImageProcessor();
                $processor->read($fullPath);

                // Resize/Crop using Ave ImageProcessor methods
                if ($width && $height) {
                    $processor->cover((int)$width, (int)$height); // Crop to exact size
                } elseif ($width || $height) {
                    $processor->scale($width ? (int)$width : null, $height ? (int)$height : null); // Maintain aspect ratio
                }

                // Encode with format and quality
                $encoded = $processor->encode($format, $quality);

                // Ensure thumbnails directory exists
                $targetDir = dirname($target_path_full);
                if (!$disk->exists($targetDir)) {
                    $disk->makeDirectory($targetDir);
                }

                // Save to storage
                $disk->put($target_path_full, $encoded);
            } catch (\Exception $e) {
                return $origin_url;
            }
        }

        return '/storage/' . $target_path_full;
    }
}
