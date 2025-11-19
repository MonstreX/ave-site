<?php

namespace Monstrex\AveSite\Resources\Block;

use Monstrex\AveSite\Models\Block as BlockModel;
use Monstrex\AveSite\Models\BlockRegion;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Components\Row;
use Monstrex\Ave\Core\Components\Col;
use Monstrex\Ave\Core\Components\Tabs;
use Monstrex\Ave\Core\Components\Tab;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\CodeEditor;
use Monstrex\Ave\Core\Fields\Textarea;
use Monstrex\Ave\Core\Fields\Toggle;
use Monstrex\Ave\Core\Fields\Select;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\Media;
use Monstrex\Ave\Core\Fields\FieldSet;
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
            Tabs::make()->schema([
                // Tab 1: Main
                Tab::make(__('ave-site::resources_blocks.tabs.main'))->schema([
                    Row::make()->schema([
                        Col::make(6)->schema([
                            TextInput::make('title')
                                ->label(__('ave-site::resources_blocks.fields.title'))
                                ->required(),
                        ]),
                        Col::make(3)->schema([
                            Toggle::make('status')
                                ->label(__('ave-site::resources_blocks.fields.status'))
                                ->default(true),
                        ]),
                        Col::make(3)->schema([
                            Number::make('order')
                                ->label(__('ave-site::resources_blocks.fields.order'))
                                ->default(0),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(6)->schema([
                            TextInput::make('key')
                                ->label(__('ave-site::resources_blocks.fields.key'))
                                ->required(),
                        ]),
                        Col::make(6)->schema([
                            Select::make('region_id')
                                ->label(__('ave-site::resources_blocks.fields.region'))
                                ->options(static::getRegionOptions())
                                ->required(),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(12)->schema([
                            CodeEditor::make('content')
                                ->label(__('ave-site::resources_blocks.fields.content'))
                                ->language('html')
                                ->height(150)
                                ->theme('monokai')
                                ->autoHeight(true),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(12)->schema([
                            Media::make('images')
                                ->label(__('ave-site::resources_blocks.fields.images'))
                                ->help(__('ave-site::resources_blocks.fields.images_help'))
                                ->collection('block_images')
                                ->multiple(true, maxFiles: 50)
                                ->columns(6)
                                ->acceptImages()
                                ->maxFileSize(5120)
                                ->props('alt', 'title', 'content', 'link'),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(12)->schema([
                            Textarea::make('urls')
                                ->label(__('ave-site::resources_blocks.fields.urls'))
                                ->rows(4),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(6)->schema([
                            Select::make('rules')
                                ->label(__('ave-site::resources_blocks.fields.rules'))
                                ->options([
                                    '0' => __('ave-site::resources_blocks.rules_hide'),
                                    '1' => __('ave-site::resources_blocks.rules_show'),
                                ])
                                ->default(0),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(12)->schema([
                            CodeEditor::make('options')
                                ->label(__('ave-site::resources_blocks.fields.options'))
                                ->language('json')
                                ->height(150)
                                ->theme('monokai')
                                ->autoHeight(true),
                        ]),
                    ]),
                ]),

                // Tab 2: Elements
                Tab::make(__('ave-site::resources_blocks.tabs.elements'))->schema([
                    Row::make()->schema([
                        Col::make(12)->schema([
                            FieldSet::make('elements')
                        ->label(__('ave-site::resources_blocks.fields.elements'))
                        ->help(__('ave-site::resources_blocks.fields.elements_help'))
                        ->displayAs('cards')
                        ->sortable()
                        ->minItems(0)
                        ->maxItems(50)
                        ->columns(6)
                        ->headTitle('title')
                        ->headPreview('image')
                        ->schema([
                            Div::make('row')->schema([
                                Div::make('col-6')->schema([
                                    TextInput::make('title')
                                        ->label(__('ave-site::resources_blocks.element_fields.title'))
                                        ->required(),
                                ]),
                                Div::make('col-6')->schema([
                                    TextInput::make('subtitle')
                                        ->label(__('ave-site::resources_blocks.element_fields.subtitle')),
                                ]),
                                Div::make('col-6')->schema([
                                    TextInput::make('alt')
                                        ->label(__('ave-site::resources_blocks.element_fields.alt')),
                                ]),
                                Div::make('col-6')->schema([
                                    TextInput::make('link')
                                        ->label(__('ave-site::resources_blocks.element_fields.link')),
                                ]),
                            ]),
                            CodeEditor::make('content')
                                ->label(__('ave-site::resources_blocks.fields.content'))
                                ->language('html')
                                ->height(150)
                                ->theme('monokai')
                                ->autoHeight(true),
                            Media::make('image')
                                ->label(__('ave-site::resources_blocks.element_fields.image'))
                                ->collection('block_elements')
                                ->multiple(true, maxFiles: 50)
                                ->acceptImages()
                                ->maxFileSize(5120)
                                ->props('alt'),
                        ]),
                        ]),
                    ]),
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
