<?php

namespace Monstrex\AveSite\Services;

class DataSourceService
{
    /**
     * Загрузка множественных DataSources
     *
     * @param array $datasources Ассоциативный массив [key => config]
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
                // Простая переменная (не модель)
                $data[$key] = $config;
            }
        }

        return $data;
    }

    /**
     * Загрузка одного DataSource
     *
     * @param array $config Конфигурация: model, where, order, limit, with, random
     * @return array
     */
    protected function getDataSource(array $config): array
    {
        $modelClass = $config['model'];

        if (!class_exists($modelClass)) {
            return [];
        }

        $query = $modelClass::query();

        // Where (множественные условия)
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

        // Limit (после получения, для совместимости с Voyager Site)
        if (isset($config['limit'])) {
            $collection = $collection->take($config['limit']);
        }

        return $collection->toArray();
    }
}
