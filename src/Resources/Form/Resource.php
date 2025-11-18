<?php

namespace Monstrex\AveSite\Resources\Form;

use Monstrex\AveSite\Models\Form as FormModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Textarea;
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
            Column::make('title')
                ->label(__('ave-site::resources_forms.columns.title'))
                ->sortable(true),
            Column::make('key')
                ->label(__('ave-site::resources_forms.columns.key'))
                ->sortable(true),
            Column::make('status')
                ->label(__('ave-site::resources_forms.columns.status'))
                ->format(fn ($value) => $value ? __('ave::common.yes') : __('ave::common.no'))
                ->sortable(true),
            Column::make('order')
                ->label(__('ave-site::resources_forms.columns.order'))
                ->sortable(true),
        ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('title')
                        ->label(__('ave-site::resources_forms.fields.title'))
                        ->required(),
                ]),
                Div::make('col-12 col-md-3')->schema([
                    Toggle::make('status')
                        ->label(__('ave-site::resources_forms.fields.status'))
                        ->default(true),
                ]),
                Div::make('col-12 col-md-3')->schema([
                    Number::make('order')
                        ->label(__('ave-site::resources_forms.fields.order'))
                        ->default(0),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('key')
                        ->label(__('ave-site::resources_forms.fields.key'))
                        ->required(),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('content')
                        ->label(__('ave-site::resources_forms.fields.content'))
                        ->rows(12),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('details')
                        ->label(__('ave-site::resources_forms.fields.details'))
                        ->rows(10),
                ]),
            ]),
        ]);
    }
}
