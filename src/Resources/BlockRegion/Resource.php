<?php

namespace Monstrex\AveSite\Resources\BlockRegion;

use Monstrex\AveSite\Models\BlockRegion as BlockRegionModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Row;
use Monstrex\Ave\Core\Components\Col;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\ColorPicker;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = BlockRegionModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-resize-full';
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
        return static::$group ?? __('ave-site::resources_groups.settings');
    }

    public static function table($context): Table
    {
        return Table::make()->columns([
            Column::make('title')
                ->label(__('ave-site::resources_block_regions.columns.title'))
                ->linkAction('edit')
                ->sortable(true),
            Column::make('key')
                ->label(__('ave-site::resources_block_regions.columns.key'))
                ->sortable(true),
            Column::make('order')
                ->label(__('ave-site::resources_block_regions.columns.order'))
                ->sortable(true),
            Column::make('color')
                ->label(__('ave-site::resources_block_regions.columns.color'))
                ->format(fn ($value) => $value ? '<span style="display:inline-block;width:20px;height:20px;background:'.$value.';border-radius:3px;"></span>' : '')
                ->html(true),
        ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Row::make()->schema([
                Col::make(6)->schema([
                    TextInput::make('title')
                        ->label(__('ave-site::resources_block_regions.fields.title'))
                        ->required(),
                ]),
                Col::make(6)->schema([
                    TextInput::make('key')
                        ->label(__('ave-site::resources_block_regions.fields.key'))
                        ->required(),
                ]),
            ]),
            Row::make()->schema([
                Col::make(4)->schema([
                    ColorPicker::make('color')
                        ->label(__('ave-site::resources_block_regions.fields.color')),
                    
                ]),
            ]),
            Row::make()->schema([
                Col::make(2)->schema([
                    Number::make('order')
                        ->label(__('ave-site::resources_block_regions.fields.order'))
                        ->default(0),
                ]),
            ]),
        ]);
    }
}
