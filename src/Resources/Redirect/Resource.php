<?php

namespace Monstrex\AveSite\Resources\Redirect;

use Monstrex\AveSite\Models\Redirect;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Columns\BooleanColumn;
use Monstrex\Ave\Core\Components\Col;
use Monstrex\Ave\Core\Components\Row;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\Select;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Toggle;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = Redirect::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-forward';
    public static ?string $slug = 'site-redirects';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_redirects.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_redirects.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.seo');
    }

    public static function table($context): Table
    {
        return Table::make()
            ->columns([
                Column::make('id')
                    ->label(__('ave-site::resources_redirects.columns.id'))
                    ->sortable(true)
                    ->width(60),
                BooleanColumn::make('is_active')
                    ->label(__('ave-site::resources_redirects.columns.is_active'))
                    ->trueLabel(__('ave::common.active'))
                    ->falseLabel(__('ave::common.inactive'))
                    ->inlineToggle(),
                Column::make('from_url')
                    ->label(__('ave-site::resources_redirects.columns.from_url'))
                    ->bold()
                    ->linkAction('edit'),
                Column::make('to_url')
                    ->label(__('ave-site::resources_redirects.columns.to_url')),
                Column::make('status_code')
                    ->label(__('ave-site::resources_redirects.columns.status_code'))
                    ->width(80),
                Column::make('hits')
                    ->label(__('ave-site::resources_redirects.columns.hits'))
                    ->sortable(true)
                    ->width(80),
            ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Row::make()->schema([
                Col::make(2)->schema([
                    Toggle::make('is_active')
                        ->label(__('ave-site::resources_redirects.fields.is_active'))
                        ->default(true),
                ]),
            ]),
            Row::make()->schema([
                Col::make(6)->schema([
                    TextInput::make('from_url')
                        ->label(__('ave-site::resources_redirects.fields.from_url'))
                        ->placeholder('/old-page')
                        ->required(),
                ]),
                Col::make(6)->schema([
                    TextInput::make('to_url')
                        ->label(__('ave-site::resources_redirects.fields.to_url'))
                        ->placeholder('/new-page or https://...')
                        ->required(),
                ]),
            ]),
            Row::make()->schema([
                Col::make(4)->schema([
                    Select::make('status_code')
                        ->label(__('ave-site::resources_redirects.fields.status_code'))
                        ->options([
                            301 => '301 - ' . __('ave-site::resources_redirects.status_codes.301'),
                            302 => '302 - ' . __('ave-site::resources_redirects.status_codes.302'),
                            307 => '307 - ' . __('ave-site::resources_redirects.status_codes.307'),
                            308 => '308 - ' . __('ave-site::resources_redirects.status_codes.308'),
                        ])
                        ->default(301),
                ]),
                Col::make(4)->schema([
                    Number::make('hits')
                        ->label(__('ave-site::resources_redirects.fields.hits'))
                        ->default(0)
                        ->disabled()
                        ->dehydrated(false),
                ]),
                Col::make(4)->schema([
                    TextInput::make('last_hit_at')
                        ->label(__('ave-site::resources_redirects.fields.last_hit_at'))
                        ->disabled()
                        ->dehydrated(false),
                ]),
            ]),
        ]);
    }
}
