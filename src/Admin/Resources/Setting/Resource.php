<?php

namespace Monstrex\AveSite\Admin\Resources\Setting;

use Monstrex\AveSite\Models\Setting as SettingModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Textarea;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = SettingModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-tools';
    public static ?string $slug = 'site-settings';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_settings.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_settings.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.settings');
    }

    public static function table($context): Table
    {
        return Table::make()->columns([
            Column::make('group')
                ->label(__('ave-site::resources_settings.columns.group'))
                ->sortable(true),
            Column::make('name')
                ->label(__('ave-site::resources_settings.columns.name'))
                ->sortable(true),
            Column::make('created_at')
                ->label(__('ave-site::resources_settings.columns.created_at'))
                ->format(fn ($value) => optional($value)?->format('Y-m-d H:i'))
                ->sortable(true),
        ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('group')
                        ->label(__('ave-site::resources_settings.fields.group'))
                        ->required(),
                ]),
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('name')
                        ->label(__('ave-site::resources_settings.fields.name'))
                        ->required(),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('fields')
                        ->label(__('ave-site::resources_settings.fields.fields'))
                        ->rows(15),
                ]),
            ]),
        ]);
    }
}
