<?php

namespace Monstrex\AveSite\Resources\Page;

use Monstrex\AveSite\Models\Page as PageModel;
use Monstrex\Ave\Core\Columns\Column;
use Monstrex\Ave\Core\Columns\BooleanColumn;
use Monstrex\Ave\Core\Components\Col;
use Monstrex\Ave\Core\Components\Row;
use Monstrex\Ave\Core\Components\Tab;
use Monstrex\Ave\Core\Components\Tabs;
use Monstrex\Ave\Core\Fields\CodeEditor;
use Monstrex\Ave\Core\Fields\DateTimePicker;
use Monstrex\Ave\Core\Fields\Media;
use Monstrex\Ave\Core\Fields\Number;
use Monstrex\Ave\Core\Fields\RichEditor;
use Monstrex\Ave\Core\Fields\Select;
use Monstrex\Ave\Core\Fields\TextInput;
use Monstrex\Ave\Core\Fields\Toggle;
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
        return Table::make()
            ->tree('parent_id', 'order', 8)
            ->columns([
                Column::make('id')
                    ->label(__('ave-site::resources_pages.columns.id'))
                    ->sortable(true)
                    ->width(60),
                BooleanColumn::make('status')
                    ->label(__('ave-site::resources_pages.columns.status'))
                    ->trueLabel(__('ave::common.active'))
                    ->falseLabel(__('ave::common.inactive'))
                    ->trueValue(1)
                    ->falseValue(0)
                    ->inlineToggle(),
                Column::make('title')
                    ->label(__('ave-site::resources_pages.columns.title'))
                    ->bold()
                    ->linkAction('edit'),
                Column::make('slug')
                    ->label(__('ave-site::resources_pages.columns.slug'))
                    ->sortable(true)
            ]);
    }

    public static function form($context): Form
    {
        return Form::make()->schema([
            Tabs::make()->schema([
                Tab::make(__('ave-site::resources_pages.tabs.main'))->schema([
                    Row::make()->schema([
                        Col::make(3)->schema([
                            Toggle::make('status')
                                ->label(__('ave-site::resources_pages.fields.status'))
                                ->default(true),
                        ]),
                        Col::make(3)->schema([
                            Toggle::make('menu')
                                ->label(__('ave-site::resources_pages.fields.menu'))
                                ->default(true),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(8)->schema([
                            TextInput::make('title')
                                ->label(__('ave-site::resources_pages.fields.title'))
                                ->required(),
                        ]),
                        Col::make(4)->schema([
                            TextInput::make('slug')
                                ->label(__('ave-site::resources_pages.fields.slug'))
                                ->required(),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(6)->schema([
                            Select::make('parent_id')
                                ->label(__('ave-site::resources_pages.fields.parent'))
                                ->options(static::getParentOptions($context))
                                ->placeholder(__('ave-site::resources_pages.no_parent')),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(12)->schema([
                            RichEditor::make('content')
                                ->label(__('ave-site::resources_pages.fields.content'))
                                ->height(400),
                        ]),
                    ]),
                ]),
                Tab::make(__('ave-site::resources_pages.tabs.media'))->schema([
                    Row::make()->schema([
                        Col::make(6)->schema([
                            Media::make('main_image')
                                ->label(__('ave-site::resources_pages.fields.image'))
                                ->collection('page-main')
                                ->acceptImages()
                                ->columns(8),
                        ]),
                        Col::make(6)->schema([
                            Media::make('gallery')
                                ->label(__('ave-site::resources_pages.fields.images'))
                                ->collection('page-gallery')
                                ->multiple(true)
                                ->columns(8)
                                ->acceptImages(),
                        ]),
                    ]),
                ]),
                Tab::make(__('ave-site::resources_pages.tabs.seo'))->schema([
                    Row::make()->schema([
                        Col::make(6)->schema([
                            TextInput::make('seo_title')
                                ->statePath('seo.seo_title')
                                ->label(__('ave-site::resources_pages.fields.seo_title')),
                        ]),
                        Col::make(6)->schema([
                            TextInput::make('seo_keywords')
                                ->statePath('seo.meta_keywords')
                                ->label(__('ave-site::resources_pages.fields.seo_keywords')),
                        ]),
                    ]),
                    Row::make()->schema([
                        Col::make(12)->schema([
                            TextInput::make('seo_description')
                                ->statePath('seo.meta_description')
                                ->label(__('ave-site::resources_pages.fields.seo_description')),
                        ]),
                    ]),
                ]),
                Tab::make(__('ave-site::resources_pages.tabs.options'))->schema([
                    Row::make()->schema([
                        Col::make(12)->schema([
                            CodeEditor::make('details')
                                ->label(__('ave-site::resources_pages.fields.details'))
                                ->language('json')
                                ->theme('github')
                                ->height(200)
                                ->autoHeight(true),
                        ]),
                    ]),
                ]),
                Tab::make(__('ave-site::resources_pages.tabs.additional'))->schema([
                    Row::make()->schema([
                        Col::make(6)->schema([
                            Number::make('order')
                                ->label(__('ave-site::resources_pages.fields.order'))
                                ->min(0),
                        ]),
                        Col::make(6)->schema([
                            DateTimePicker::make('created_at')
                                ->label(__('ave-site::resources_pages.fields.published_at'))
                                ,
                        ]),
                    ]),
                ]),
            ]),
        ]);
    }

    protected static function getParentOptions($context): array
    {
        $query = PageModel::orderBy('title');

        $record = null;
        if ($context && method_exists($context, 'record')) {
            $record = $context->record();
        } elseif ($context && method_exists($context, 'getRecord')) {
            $record = $context->getRecord();
        }

        if ($record && $record->exists) {
            $query->where('id', '<>', $record->id);
        }

        return $query->pluck('title', 'id')->toArray();
    }
}
