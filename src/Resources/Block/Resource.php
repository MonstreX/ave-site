<?php

namespace Monstrex\AveSite\Resources\Block;

use Monstrex\AveSite\Models\Block as BlockModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Columns\BooleanColumn;
use Monstrex\Ave\Core\Columns\ComputedColumn;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Components\Row;
use Monstrex\Ave\Core\Components\Col;
use Monstrex\Ave\Core\Components\Tabs;
use Monstrex\Ave\Core\Components\Tab;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\CodeEditor;
use Monstrex\Ave\Core\Fields\Textarea;
use Monstrex\Ave\Core\Fields\Toggle;
use Monstrex\Ave\Core\Fields\RadioGroup;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\Media;
use Monstrex\Ave\Core\Fields\FieldSet;
use Monstrex\Ave\Core\Fields\BelongsToSelect;
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
        return Table::make()
            ->sortable('order')
            ->columns([
                BooleanColumn::make('status')
                    ->label(__('ave-site::resources_blocks.columns.status'))
                    ->trueValue(1)
                    ->falseValue(0)
                    ->width('60')
                    ->inlineToggle(),
                Column::make('title')
                    ->label(__('ave-site::resources_blocks.columns.title'))
                    ->linkAction('edit')
                    ->sortable(true),
                Column::make('key')
                    ->label(__('ave-site::resources_blocks.columns.key'))
                    ->sortable(true),
                ComputedColumn::make('region_badge')
                    ->label(__('ave-site::resources_blocks.columns.region'))
                    ->compute(function ($record) {
                        if (!$record->region) {
                            return '';
                        }

                        $color = $record->region->color ?? '#6c757d';
                        $title = $record->region->title;

                        // Calculate inverted color for text
                        $hex = ltrim($color, '#');
                        $r = hexdec(substr($hex, 0, 2));
                        $g = hexdec(substr($hex, 2, 2));
                        $b = hexdec(substr($hex, 4, 2));
                        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                        $textColor = $brightness > 155 ? '#000000' : '#ffffff';

                        return "<span class=\"badge\" style=\"background-color: {$color}; color: {$textColor};\">{$title}</span>";
                    })
                    ->html()
                    ->sortable(false),
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
                        Col::make(2)->schema([
                            Toggle::make('status')
                                ->label(__('ave-site::resources_blocks.fields.status'))
                                ->default(true),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(4)->schema([
                            TextInput::make('title')
                                ->label(__('ave-site::resources_blocks.fields.title'))
                                ->required(),
                        ]),
                        Col::make(4)->schema([
                            TextInput::make('key')
                                ->label(__('ave-site::resources_blocks.fields.key'))
                                ->required(),
                        ]),
                        Col::make(4)->schema([
                            BelongsToSelect::make('region_id')
                                ->label(__('ave-site::resources_blocks.fields.region'))
                                ->relationship('region', 'title')
                                ->nullable(),
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
                        Col::make(4)->schema([
                            RadioGroup::make('rules')
                                ->label(__('ave-site::resources_blocks.fields.rules'))
                                ->options([
                                    '0' => __('ave-site::resources_blocks.rules_hide'),
                                    '1' => __('ave-site::resources_blocks.rules_show'),
                                ])
                                ->inline()
                                ->default(0),
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
                        Col::make(12)->schema([
                            CodeEditor::make('details')
                                ->label(__('ave-site::resources_blocks.fields.details'))
                                ->language('json')
                                ->height(150)
                                ->theme('github')
                                ->autoHeight(true),
                        ]),
                        Col::make(3)->schema([
                            Number::make('order')
                                ->label(__('ave-site::resources_blocks.fields.order'))
                                ->default(0),
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

    protected static array $cloneable = [
        'title' => ' (copy)',
        'key' => '-copy',
        'region_id',
        'order',
        'status',
        'content',
        'urls',
        'rules',
        'details',
        'elements',
    ];
}
