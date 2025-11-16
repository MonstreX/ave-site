<?php

namespace Monstrex\AveSite\Admin\Resources\Page;

use Monstrex\AveSite\Models\Page as PageModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Components\Div;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Textarea;
use Monstrex\Ave\Core\Fields\Toggle;
use Monstrex\Ave\Core\Fields\Select;
use Monstrex\Ave\Core\Form;
use Monstrex\Ave\Core\Resource as BaseResource;
use Monstrex\Ave\Core\Table;

class Resource extends BaseResource
{
    public static ?string $model = PageModel::class;
    public static ?string $label = null;
    public static ?string $singularLabel = null;
    public static ?string $icon = 'voyager-file-text';
    public static ?string $slug = 'site-pages';
    public static ?string $group = null;

    public static function getLabel(): string
    {
        return static::$label ?? __('ave-site::resources_pages.label');
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel ?? __('ave-site::resources_pages.singular');
    }

    public static function getGroup(): ?string
    {
        return static::$group ?? __('ave-site::resources_groups.content');
    }

    public static function table($context): Table
    {
        return Table::make()->columns([
            Column::make('title')
                ->label(__('ave-site::resources_pages.columns.title'))
                ->sortable(true),
            Column::make('slug')
                ->label(__('ave-site::resources_pages.columns.slug'))
                ->sortable(true),
            Column::make('status')
                ->label(__('ave-site::resources_pages.columns.status'))
                ->format(fn ($value) => $value ? __('ave::common.yes') : __('ave::common.no'))
                ->sortable(true),
            Column::make('created_at')
                ->label(__('ave-site::resources_pages.columns.created_at'))
                ->format(fn ($value) => optional($value)?->format('Y-m-d H:i'))
                ->sortable(true),
        ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Div::make('row')->schema([
                Div::make('col-12 col-md-8')->schema([
                    TextInput::make('title')
                        ->label(__('ave-site::resources_pages.fields.title'))
                        ->required(),
                ]),
                Div::make('col-12 col-md-4')->schema([
                    Toggle::make('status')
                        ->label(__('ave-site::resources_pages.fields.status'))
                        ->default(true),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('slug')
                        ->label(__('ave-site::resources_pages.fields.slug'))
                        ->required(),
                ]),
                Div::make('col-12 col-md-6')->schema([
                    Select::make('parent_id')
                        ->label(__('ave-site::resources_pages.fields.parent'))
                        ->options(static::getParentOptions())
                        ->default(-1),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('content')
                        ->label(__('ave-site::resources_pages.fields.content'))
                        ->rows(15),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('options')
                        ->label(__('ave-site::resources_pages.fields.options'))
                        ->rows(8),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('seo_title')
                        ->label(__('ave-site::resources_pages.fields.seo_title')),
                ]),
                Div::make('col-12 col-md-6')->schema([
                    TextInput::make('seo_keywords')
                        ->label(__('ave-site::resources_pages.fields.seo_keywords')),
                ]),
            ]),
            Div::make('row')->schema([
                Div::make('col-12')->schema([
                    Textarea::make('seo_description')
                        ->label(__('ave-site::resources_pages.fields.seo_description'))
                        ->rows(3),
                ]),
            ]),
        ]);
    }

    protected static function getParentOptions(): array
    {
        $pages = PageModel::orderBy('title')->get();
        $options = ['-1' => __('ave-site::resources_pages.no_parent')];

        foreach ($pages as $page) {
            $options[$page->id] = $page->title;
        }

        return $options;
    }
}
