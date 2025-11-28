<?php

namespace Monstrex\AveSite\Services;

use Monstrex\Ave\Core\Fields\Media;
use Monstrex\Ave\Core\Fields\Fieldset;
use Monstrex\Ave\Core\Fields\CodeEditor;

/**
 * Universal model resolver for Liquid templates
 * Converts models to arrays with resolved Media, FieldSet, and JSON fields
 */
class ModelResolver
{
    /**
     * Cache for Resource field metadata
     * @var array<string, array>
     */
    protected static array $metadataCache = [];

    /**
     * Cache for Resource forms
     * @var array<string, \Monstrex\Ave\Core\Form>
     */
    protected static array $formCache = [];

    /**
     * Resolve model to array for Liquid template
     * Automatically processes Media, FieldSet, and JSON fields based on Resource metadata
     *
     * @param mixed $model Model instance
     * @param string|null $resourceClass Resource class name (optional, will be auto-detected)
     * @return array Resolved model data
     */
    public function resolveModel($model, ?string $resourceClass = null): array
    {
        if (!$model) {
            return [];
        }

        // Convert model to array
        $data = $model->toArray();

        // Get Resource metadata (cached)
        $metadata = $this->getResourceFieldsMetadata($model, $resourceClass);

        if (!empty($metadata)) {
            // Process fields using Resource metadata
            foreach ($metadata as $fieldName => $fieldInfo) {
                $value = $data[$fieldName] ?? null;
                $data[$fieldName] = $this->processField($value, $fieldInfo, $model);
            }
        } else {
            // Fallback: No Resource found - use HasFieldSet trait if available
            if (method_exists($model, 'getFieldSet')) {
                // Try to auto-detect FieldSet fields (array with elements containing "elements.X.collection")
                foreach ($data as $fieldName => $value) {
                    if (is_array($value) && $this->looksLikeFieldSet($value)) {
                        $data[$fieldName] = $model->getFieldSet($fieldName);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get field metadata from Resource (with caching)
     *
     * @param mixed $model Model instance
     * @param string|null $resourceClass Resource class name
     * @return array Field metadata: ['field_name' => ['type' => '...', 'config' => [...]]]
     */
    protected function getResourceFieldsMetadata($model, ?string $resourceClass = null): array
    {
        $modelClass = get_class($model);

        // Check cache
        if (isset(static::$metadataCache[$modelClass])) {
            return static::$metadataCache[$modelClass];
        }

        // Auto-detect Resource if not provided
        if (!$resourceClass) {
            $resourceClass = $this->findResourceForModel($modelClass);
        }

        if (!$resourceClass || !class_exists($resourceClass)) {
            static::$metadataCache[$modelClass] = [];
            return [];
        }

        // Get Form from Resource (cached)
        try {
            if (!isset(static::$formCache[$resourceClass])) {
                static::$formCache[$resourceClass] = $resourceClass::form(null);
            }
            $form = static::$formCache[$resourceClass];
        } catch (\Exception $e) {
            static::$metadataCache[$modelClass] = [];
            return [];
        }

        // Get all fields from Form (flattened, ignoring wrappers)
        $allFields = [];
        foreach ($form->layout() as $layoutItem) {
            $component = $layoutItem['component'];
            if (method_exists($component, 'flattenFields')) {
                $allFields = array_merge($allFields, $component->flattenFields());
            }
        }

        // Build metadata from fields
        $metadata = [];
        foreach ($allFields as $field) {
            if (method_exists($field, 'key')) {
                $fieldName = $field->key();

                if ($fieldName) {
                    $fieldType = $this->getFieldType($field);

                    $metadata[$fieldName] = [
                        'type' => $fieldType,
                        'component' => $field,
                    ];
                }
            }
        }

        // Cache and return
        static::$metadataCache[$modelClass] = $metadata;
        return $metadata;
    }

    /**
     * Determine field type from component class
     *
     * @param mixed $component Field component
     * @return string Field type: 'media', 'fieldset', 'json', or 'default'
     */
    protected function getFieldType($component): string
    {
        if ($component instanceof Media) {
            return 'media';
        }

        if ($component instanceof Fieldset) {
            return 'fieldset';
        }

        // CodeEditor with JSON language is treated as JSON field
        if ($component instanceof CodeEditor) {
            if (method_exists($component, 'getLanguage') && $component->getLanguage() === 'json') {
                return 'json';
            }
        }

        return 'default';
    }

    /**
     * Process field value according to its type
     *
     * @param mixed $value Field value
     * @param array $fieldInfo Field metadata
     * @param mixed $model Model instance
     * @return mixed Processed value
     */
    protected function processField($value, array $fieldInfo, $model)
    {
        $type = $fieldInfo['type'];

        switch ($type) {
            case 'media':
                return $this->processMediaField($value, $fieldInfo, $model);

            case 'fieldset':
                return $this->processFieldSetField($value, $fieldInfo, $model);

            case 'json':
                return $this->processJsonField($value);

            default:
                return $value;
        }
    }

    /**
     * Process Media field
     *
     * @param mixed $value Field value
     * @param array $fieldInfo Field metadata
     * @param mixed $model Model instance
     * @return array Media array
     */
    protected function processMediaField($value, array $fieldInfo, $model): array
    {
        if (!method_exists($model, 'getMedia')) {
            return [];
        }

        $component = $fieldInfo['component'];
        $collection = method_exists($component, 'getCollection') ? $component->getCollection() : null;

        if (!$collection) {
            return [];
        }

        try {
            $media = $model->getMedia($collection);

            if ($media->isEmpty()) {
                return [];
            }

            return $media->map(fn($m) => $this->mediaToArray($m))->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Process FieldSet field with nested Media
     *
     * Uses HasFieldSet trait if available on the model.
     *
     * @param mixed $value Field value (array of elements)
     * @param array $fieldInfo Field metadata
     * @param mixed $model Model instance
     * @return array Processed FieldSet elements
     */
    protected function processFieldSetField($value, array $fieldInfo, $model): array
    {
        if (!is_array($value)) {
            return [];
        }

        // Use HasFieldSet trait method if available
        if (method_exists($model, 'getFieldSet')) {
            $fieldName = $fieldInfo['component']->key();
            return $model->getFieldSet($fieldName);
        }

        // Fallback: return raw value
        return $value;
    }

    /**
     * Process JSON field
     *
     * @param mixed $value Field value (JSON string)
     * @return mixed Decoded JSON or original value
     */
    protected function processJsonField($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return $decoded ?? $value;
    }

    /**
     * Convert Media object to array for Liquid templates
     *
     * @param \Monstrex\Ave\Models\Media $media
     * @return array
     */
    protected function mediaToArray($media): array
    {
        return [
            'url' => $media->url(),
            'fullUrl' => $media->fullUrl(),
            'path' => $media->path(),
            'fileName' => $media->fileName(),
            'size' => $media->size(),
            'mime' => $media->mime(),
            'order' => $media->order(),
            'props' => $media->props(), // stdClass with ALL props
        ];
    }

    /**
     * Check if array looks like a FieldSet (contains elements with "elements.X.collection" strings)
     *
     * @param array $value
     * @return bool
     */
    protected function looksLikeFieldSet(array $value): bool
    {
        // Check if it's an array of arrays (typical FieldSet structure)
        if (empty($value) || !is_array($value[0] ?? null)) {
            return false;
        }

        // Check if any element contains Media collection names (strings starting with "elements.")
        foreach ($value as $element) {
            if (!is_array($element)) {
                continue;
            }

            foreach ($element as $fieldValue) {
                if (is_string($fieldValue) && str_starts_with($fieldValue, 'elements.')) {
                    return true; // Found a Media collection reference
                }
            }
        }

        return false;
    }

    /**
     * Find Resource class for given Model class
     *
     * Searches in ALL namespaces: ResourceRegistry, ave, ave-site, and user namespaces
     *
     * @param string $modelClass Model class name (e.g., "App\Models\Article")
     * @return string|null Resource class name
     */
    protected function findResourceForModel(string $modelClass): ?string
    {
        // 1. Try to find Resource in ResourceRegistry (registered resources)
        try {
            $registry = app(\Monstrex\Ave\Core\Registry\ResourceRegistry::class);
            $resources = $registry->all();

            foreach ($resources as $resource) {
                if ($resource::$model === $modelClass) {
                    return $resource;
                }
            }
        } catch (\Exception $e) {
            // Registry not available
        }

        // 2. Try to guess Resource location by checking ALL known namespaces
        $modelName = class_basename($modelClass);

        // Possible Resource locations to check (in order of priority)
        $possibleResources = [
            // User namespace (App\Models\X -> App\Ave\Resources\X\Resource)
            "App\\Ave\\Resources\\{$modelName}\\Resource",

            // Ave Site package (Monstrex\AveSite\Models\X -> Monstrex\AveSite\Resources\X\Resource)
            "Monstrex\\AveSite\\Resources\\{$modelName}\\Resource",

            // Ave Core package (Monstrex\Ave\Models\X -> Monstrex\Ave\Resources\X\Resource)
            "Monstrex\\Ave\\Resources\\{$modelName}\\Resource",
        ];

        foreach ($possibleResources as $resourceClass) {
            if (class_exists($resourceClass)) {
                return $resourceClass;
            }
        }

        return null;
    }
}
