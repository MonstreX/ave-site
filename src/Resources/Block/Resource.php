<?php

namespace Monstrex\AveSite\Resources\Block;

use Monstrex\AveSite\Models\Block as BlockModel;
use Monstrex\AveSite\Models\BlockRegion;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Textarea;
use Monstrex\Ave\Core\Fields\Toggle;
use Monstrex\Ave\Core\Fields\Select;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = BlockModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-puzzle';
    public static ?string $slug = 'site-blocks';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_blocks.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_blocks.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.content');
    }

    public static function table($context): Table
    {
        return Table::make()->columns([
            Column::make('title')
                ->label(__('ave-site::resources_blocks.columns.title'))
                ->sortable(true),
            Column::make('key')
                ->label(__('ave-site::resources_blocks.columns.key'))
                ->sortable(true),
            Column::make('region.name')
                ->label(__('ave-site::resources_blocks.columns.region'))
                ->sortable(false),
            Column::make('status')
                ->label(__('ave-site::resources_blocks.columns.status'))
                ->format(fn ($value) => $value ? __('ave::common.yes') : __('ave::common.no'))
                ->sortable(true),
            Column::make('order')
                ->label(__('ave-site::resources_blocks.columns.order'))
                ->sortable(true),
        ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('title')
                        ->label(__('ave-site::resources_blocks.fields.title'))
                        ->required(),
                ]),
                Div::make('col-12 col-md-3')->schema([
                    Toggle::make('status')
                        ->label(__('ave-site::resources_blocks.fields.status'))
                        ->default(true),
                ]),
                Div::make('col-12 col-md-3')->schema([
                    Number::make('order')
                        ->label(__('ave-site::resources_blocks.fields.order'))
                        ->default(0),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('key')
                        ->label(__('ave-site::resources_blocks.fields.key'))
                        ->required(),
                ]),
                Div::make('col-12 col-md-6')->schema([
                    Select::make('region_id')
                        ->label(__('ave-site::resources_blocks.fields.region'))
                        ->options(static::getRegionOptions())
                        ->required(),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('content')
                        ->label(__('ave-site::resources_blocks.fields.content'))
                        ->rows(15),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('urls')
                        ->label(__('ave-site::resources_blocks.fields.urls'))
                        ->rows(4),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    Select::make('rules')
                        ->label(__('ave-site::resources_blocks.fields.rules'))
                        ->options([
                            '0' => __('ave-site::resources_blocks.rules_hide'),
                            '1' => __('ave-site::resources_blocks.rules_show'),
                        ])
                        ->default(0),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('options')
                        ->label(__('ave-site::resources_blocks.fields.options'))
                        ->rows(8),
                ]),
            ]),
        ]);
    }

    protected static function getRegionOptions(): array
    {
        $regions = BlockRegion::orderBy('name')->get();
        $options = [];

        foreach ($regions as $region) {
            $options[$region->id] = $region->name;
        }

        return $options;
    }
}
