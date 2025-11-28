<?php

namespace Monstrex\AveSite\Resources\Script;

use Monstrex\AveSite\Models\Script;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Columns\BooleanColumn;
use Monstrex\Ave\Core\Components\Col;
use Monstrex\Ave\Core\Components\Row;
use Monstrex\Ave\Core\Fields\CodeEditor;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\Select;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Toggle;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = Script::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-code';
    public static ?string $slug = 'site-scripts';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_scripts.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_scripts.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.seo');
    }

    public static function table($context): Table
    {
        return Table::make()
            ->sortable('order')
            ->columns([
                Column::make('id')
                    ->label(__('ave-site::resources_scripts.columns.id'))
                    ->sortable(true)
                    ->width(60),
                BooleanColumn::make('status')
                    ->label(__('ave-site::resources_scripts.columns.status'))
                    ->trueLabel(__('ave::common.active'))
                    ->falseLabel(__('ave::common.inactive'))
                    ->width(60)
                    ->inlineToggle(),
                Column::make('title')
                    ->label(__('ave-site::resources_scripts.columns.title'))
                    ->bold()
                    ->linkAction('edit'),
                Column::make('key')
                    ->label(__('ave-site::resources_scripts.columns.key')),
                Column::make('position')
                    ->label(__('ave-site::resources_scripts.columns.position'))
                    ->format(fn ($value) => __('ave-site::resources_scripts.positions.' . $value)),
                Column::make('order')
                    ->label(__('ave-site::resources_scripts.columns.order'))
                    ->sortable(true)
                    ->width(80),
            ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Row::make()->schema([
                Col::make(2)->schema([
                    Toggle::make('status')
                        ->label(__('ave-site::resources_scripts.fields.status'))
                        ->default(true),
                ]),
            ]),
            Row::make()->schema([
                Col::make(4)->schema([
                    TextInput::make('title')
                        ->label(__('ave-site::resources_scripts.fields.title'))
                        ->required(),
                ]),
                Col::make(4)->schema([
                    TextInput::make('key')
                        ->label(__('ave-site::resources_scripts.fields.key'))
                        ->placeholder('google-analytics')
                        ->required(),
                ]),
                Col::make(2)->schema([
                    Select::make('position')
                        ->label(__('ave-site::resources_scripts.fields.position'))
                        ->options([
                            'head' => __('ave-site::resources_scripts.positions.head'),
                            'body_start' => __('ave-site::resources_scripts.positions.body_start'),
                            'body_end' => __('ave-site::resources_scripts.positions.body_end'),
                        ])
                        ->default('body_end')
                        ->required(),
                ]),
                Col::make(2)->schema([
                    Number::make('order')
                        ->label(__('ave-site::resources_scripts.fields.order'))
                        ->default(0),
                ]),
            ]),
            Row::make()->schema([
                Col::make(12)->schema([
                    CodeEditor::make('content')
                        ->label(__('ave-site::resources_scripts.fields.content'))
                        ->language('javascript')
                        ->theme('github')
                        ->height(400)
                        ->required(),
                ]),
            ]),
            Row::make()->schema([
                Col::make(12)->schema([
                    CodeEditor::make('details')
                        ->label(__('ave-site::resources_scripts.fields.details'))
                        ->language('json')
                        ->theme('github')
                        ->height(150)
                        ->placeholder('{"async": true, "defer": true}'),
                ]),
            ]),
        ]);
    }
}
