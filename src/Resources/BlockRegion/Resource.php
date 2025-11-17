<?php

namespace Monstrex\AveSite\Resources\BlockRegion;

use Monstrex\AveSite\Models\BlockRegion as BlockRegionModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = BlockRegionModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-crop';
    public static ?string $slug = 'site-block-regions';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_block_regions.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_block_regions.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.content');
    }

    public static function table($context): Table
    {
        return Table::make()->columns([
            Column::make('name')
                ->label(__('ave-site::resources_block_regions.columns.name'))
                ->sortable(true),
            Column::make('key')
                ->label(__('ave-site::resources_block_regions.columns.key'))
                ->sortable(true),
            Column::make('created_at')
                ->label(__('ave-site::resources_block_regions.columns.created_at'))
                ->format(fn ($value) => optional($value)?->format('Y-m-d H:i'))
                ->sortable(true),
        ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('name')
                        ->label(__('ave-site::resources_block_regions.fields.name'))
                        ->required(),
                ]),
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('key')
                        ->label(__('ave-site::resources_block_regions.fields.key'))
                        ->required(),
                ]),
            ]),
        ]);
    }
}
