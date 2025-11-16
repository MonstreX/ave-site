<?php

namespace Monstrex\AveSite\Services;

use Monstrex\AveSite\Contracts\DataContract;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Schema;

class DataService implements DataContract
{
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
        $model = $this->getModel($modelSlug);

        if (!$model) {
            $data = DB::table($modelSlug)->where($field, $value)->first();
        } else {
            $data = $model::where($field, $value)->first();
        }

        // Drop 404 Error if not found or not published (status = 0)
        if ((!$data && $fail) || (isset($data->status) && (int) $data->status !== 1 && $fail)) {
            abort(404);
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
        $model = $this->getModel($modelSlug);

        $data = $model::where([$field => $value, 'status' => 1])->orderBy($order, $direction)->get();

        return $data;
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

        return $collection->toArray();
    }

    /**
     * Get Model using given model slug (table name)
     *
     * @param string|null $modelSlug
     * @return object|null
     */
    protected function getModel(?string $modelSlug): ?object
    {
        if (!$modelSlug) {
            // Default model is Page
            $modelSlug = config('ave-site.default_model_table', 'pages');
        }

        // Try to find model by namespace convention
        // Hardcoded namespace for ave-site package models
        $namespace = 'Monstrex\\AveSite\\Models\\';
        $modelName = Str::studly(Str::singular($modelSlug));
        $modelClass = $namespace . $modelName;

        if (class_exists($modelClass)) {
            return app($modelClass);
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
        if (!$modelSlug) {
            // Default model is Page
            $modelSlug = config('ave-site.default_model_table', 'pages');
        }

        $model = $this->getModel($modelSlug);

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

        // Add HOST if not present (need for relative URLs)
        if (!isset(parse_url($image_url)['host'])) {
            $image_url = request()->getSchemeAndHttpHost() . $image_url;
        }

        // Remove HOST and Disk Part of URL if present, like: "https://host.com/storage"
        $diskConfig = Storage::disk(config('filesystems.default'))->getConfig();
        $diskUrl = is_array($diskConfig) ? $diskConfig['url'] : $diskConfig->get('url');
        $image_url = Str::replaceFirst($diskUrl, '', $image_url);

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
        $thumb = $width || $height ? 'thumbnails/' : '';

        $target_path_full = $path_info['dirname'] . '/'
            . $thumb
            . $path_info['filename']
            . $sizes
            . '.' . $format;

        if (!Storage::disk(config('filesystems.default'))->exists($image_url)) {
            return $origin_url;
        }

        if (!Storage::disk(config('filesystems.default'))->exists($target_path_full)) {
            try {
                $image = Image::make(Storage::disk(config('filesystems.default'))->get($image_url));

                if ($width && $height) {
                    $image->fit($width, $height);
                } elseif ($width || $height) {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                $image->encode($format, $quality);

                Storage::disk(config('filesystems.default'))->put($target_path_full, (string) $image, 'public');
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        return Str::replaceFirst('//', '/', Storage::url($target_path_full));
    }
}
