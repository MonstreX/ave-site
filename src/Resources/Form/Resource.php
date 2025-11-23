<?php

namespace Monstrex\AveSite\Resources\Form;

use Monstrex\Ave\Core\Columns\BooleanColumn;
use Monstrex\AveSite\Models\Form as FormModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Row;
use Monstrex\Ave\Core\Components\Col;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\CodeEditor;
use Monstrex\Ave\Core\Fields\Toggle;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = FormModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-mail';
    public static ?string $slug = 'site-forms';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_forms.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_forms.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.content');
    }

    public static function table($context): Table
    {
        return Table::make()->columns([
            BooleanColumn::make('status')
                ->label(__('ave-site::resources_forms.columns.status'))
                ->trueValue(1)
                ->falseValue(0)
                ->width('60')
                ->inlineToggle(),
            Column::make('title')
                ->label(__('ave-site::resources_forms.columns.title'))
                ->linkAction('edit')
                ->sortable(true),
            Column::make('key')
                ->label(__('ave-site::resources_forms.columns.key'))
                ->sortable(true),
            Column::make('order')
                ->label(__('ave-site::resources_forms.columns.order'))
                ->sortable(true),
        ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Row::make()->schema([
                Col::make(6)->schema([
                    Col::make(2)->schema([
                        Toggle::make('status')
                            ->label(__('ave-site::resources_forms.fields.status'))
                            ->default(true),
                    ]),
                ]),
            ]),
            Row::make()->schema([
                Col::make(6)->schema([
                    TextInput::make('title')
                        ->label(__('ave-site::resources_forms.fields.title'))
                        ->required(),
                ]),
                Col::make(6)->schema([
                    TextInput::make('key')
                        ->label(__('ave-site::resources_forms.fields.key'))
                        ->required(),
                ]),
            ]),
            Row::make()->schema([
                Col::make(12)->schema([
                    CodeEditor::make('content')
                        ->label(__('ave-site::resources_forms.fields.content'))
                        ->language('html')
                        ->theme('monokai')
                        ->height(200)
                        ->autoHeight(true),
                ]),
            ]),
            Row::make()->schema([
                Col::make(12)->schema([
                    CodeEditor::make('details')
                        ->label(__('ave-site::resources_forms.fields.details'))
                        ->language('json')
                        ->theme('github')
                        ->height(200)
                        ->autoHeight(true),
                ]),
            ]),
            Row::make()->schema([
                Col::make(2)->schema([
                    Number::make('order')
                        ->label(__('ave-site::resources_forms.fields.order'))
                        ->default(0),
                ]),
            ]),
        ]);
    }
}
