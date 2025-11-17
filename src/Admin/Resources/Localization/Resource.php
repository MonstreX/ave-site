<?php

namespace Monstrex\AveSite\Admin\Resources\Localization;

use Monstrex\AveSite\Models\Localization as LocalizationModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Textarea;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = LocalizationModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-language';
    public static ?string $slug = 'site-localizations';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_localizations.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_localizations.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.content');
    }

    public static function table($context): Table
    {
        return Table::make()->columns([
            Column::make('key')
                ->label(__('ave-site::resources_localizations.columns.key'))
                ->sortable(true),
            Column::make('en')
                ->label(__('ave-site::resources_localizations.columns.en'))
                ->format(fn ($value) => \Illuminate\Support\Str::limit($value ?? '', 100))
                ->sortable(false),
            Column::make('ru')
                ->label(__('ave-site::resources_localizations.columns.ru'))
                ->format(fn ($value) => \Illuminate\Support\Str::limit($value ?? '', 100))
                ->sortable(false),
            Column::make('created_at')
                ->label(__('ave-site::resources_localizations.columns.created_at'))
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
                        ->label(__('ave-site::resources_localizations.fields.key'))
                        ->required(),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    Textarea::make('en')
                        ->label(__('ave-site::resources_localizations.fields.en'))
                        ->rows(6),
                ]),
                Div::make('col-12 col-md-6')->schema([
                    Textarea::make('ru')
                        ->label(__('ave-site::resources_localizations.fields.ru'))
                        ->rows(6),
                ]),
            ]),
        ]);
    }
}
