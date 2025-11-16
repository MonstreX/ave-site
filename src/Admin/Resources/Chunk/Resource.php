<?php

namespace Monstrex\AveSite\Admin\Resources\Chunk;

use Monstrex\AveSite\Models\Chunk as ChunkModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Textarea;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = ChunkModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-tag';
    public static ?string $slug = 'site-chunks';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_chunks.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_chunks.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.content');
    }

    public static function table($context): Table
    {
        return Table::make()->columns([
            Column::make('key')
                ->label(__('ave-site::resources_chunks.columns.key'))
                ->sortable(true),
            Column::make('value')
                ->label(__('ave-site::resources_chunks.columns.value'))
                ->format(fn ($value) => \Illuminate\Support\Str::limit($value, 100))
                ->sortable(false),
            Column::make('created_at')
                ->label(__('ave-site::resources_chunks.columns.created_at'))
                ->format(fn ($value) => optional($value)?->format('Y-m-d H:i'))
                ->sortable(true),
        ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    TextInput::make('key')
                        ->label(__('ave-site::resources_chunks.fields.key'))
                        ->required(),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('value')
                        ->label(__('ave-site::resources_chunks.fields.value'))
                        ->rows(10),
                ]),
            ]),
        ]);
    }
}
