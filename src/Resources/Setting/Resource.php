<?php

namespace Monstrex\AveSite\Resources\Setting;

use Monstrex\AveSite\Models\Setting as SettingModel;
use Monstrex\Ave\Core\Columns\TextColumn;
use Monstrex\Ave\Core\Columns\BadgeColumn;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\Slug;
use Monstrex\Ave\Core\Fields\CodeEditor;
use Monstrex\Ave\Core\Components\Row;
use Monstrex\Ave\Core\Components\Col;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = SettingModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-settings';
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
            TextColumn::make('order')
                ->label(__('ave-site::resources_settings.columns.order'))
                ->sortable(true)
                ->align('center')
                ->width(50),

            TextColumn::make('title')
                ->label(__('ave-site::resources_settings.columns.title'))
                ->sortable(true)
                ->searchable(true)
                ->bold()
                ->linkAction('edit'),

            BadgeColumn::make('key')
                ->label(__('ave-site::resources_settings.columns.key'))
                ->uppercase(false)
                ->pill(),

            TextColumn::make('updated_at')
                ->label(__('ave-site::resources_settings.columns.updated_at'))
                ->format(fn ($value) => optional($value)?->format('Y-m-d H:i')),
        ])
        ->defaultSort('order', 'asc');
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Row::make()->schema([
                Col::make(3)->schema([
                    TextInput::make('title')
                        ->label(__('ave-site::resources_settings.fields.title'))
                        ->required(),
                ]),
                Col::make(3)->schema([
                    Slug::make('key')
                        ->label(__('ave-site::resources_settings.fields.key'))
                        ->from('title')
                        ->separator('_')
                        ->unique('ave_site_settings')
                        ->required(),
                ]),
                Col::make(3)->schema([
                    TextInput::make('group')
                        ->label(__('ave-site::resources_settings.fields.group'))
                        ->required(),
                ]),
                Col::make(3)->schema([
                    Number::make('order')
                        ->label(__('ave-site::resources_settings.fields.order'))
                        ->min(0)
                        ->required(),
                ]),
            ]),

            CodeEditor::make('fields')
                ->label(__('ave-site::resources_settings.fields.fields'))
                ->language('json')
                ->theme('github')
                ->height(400)
                ->autoHeight(true)
                ->required(),
        ]);
    }
}
