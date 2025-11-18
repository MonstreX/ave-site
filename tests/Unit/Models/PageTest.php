<?php

namespace Monstrex\AveSite\Tests\Unit\Models;

use Monstrex\AveSite\Models\Page;
use Monstrex\AveSite\Tests\TestCase;

class PageTest extends TestCase
{
    public function test_page_can_be_created(): void
    {
        $page = Page::create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test content',
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Test Page', $page->title);
        $this->assertEquals('test-page', $page->slug);
        $this->assertTrue($page->status);
        $this->assertTrue($page->menu);
    }

    public function test_page_is_root_by_default(): void
    {
        $page = Page::create([
            'title' => 'Root Page',
            'slug' => 'root',
        ]);

        $this->assertNull($page->parent_id);
    }

    public function test_page_can_have_parent(): void
    {
        $parent = Page::create([
            'title' => 'Parent',
            'slug' => 'parent',
        ]);

        $child = Page::create([
            'title' => 'Child',
            'slug' => 'child',
            'parent_id' => $parent->id,
        ]);

        $this->assertEquals($parent->id, $child->parent_id);
    }

    public function test_published_scope_filters_inactive_pages(): void
    {
        Page::create(['title' => 'Published', 'slug' => 'published', 'status' => true]);
        Page::create(['title' => 'Draft', 'slug' => 'draft', 'status' => false]);

        $published = Page::published()->get();

        $this->assertCount(1, $published);
        $this->assertEquals('Published', $published->first()->title);
    }

    public function test_roots_scope_returns_only_root_pages(): void
    {
        $root = Page::create(['title' => 'Root', 'slug' => 'root']);
        Page::create(['title' => 'Child', 'slug' => 'child', 'parent_id' => $root->id]);

        $roots = Page::roots()->get();

        $this->assertCount(1, $roots);
        $this->assertEquals('Root', $roots->first()->title);
    }

    public function test_menu_defaults_to_enabled(): void
    {
        $page = Page::create([
            'title' => 'Test',
            'slug' => 'test',
        ]);

        $this->assertTrue($page->menu);
    }

    public function test_seo_cast_returns_array(): void
    {
        $page = Page::create([
            'title' => 'SEO',
            'slug' => 'seo',
            'seo' => [
                'seo_title' => 'Custom SEO',
                'meta_description' => 'Description',
                'meta_keywords' => 'keywords',
            ],
        ]);

        $this->assertIsArray($page->seo);
        $this->assertIsArray($page->seo_meta);
        $this->assertEquals('Custom SEO', $page->seo_meta['seo_title']);
        $this->assertEquals('Description', $page->seo_meta['meta_description']);
        $this->assertEquals('keywords', $page->seo_meta['meta_keywords']);
    }

    public function test_seo_meta_helper_returns_first_item(): void
    {
        $page = Page::create([
            'title' => 'SEO',
            'slug' => 'seo-helper',
            'seo' => [
                [
                    '_id' => 0,
                    'seo_title' => 'Helper Title',
                    'meta_description' => 'Helper Desc',
                    'meta_keywords' => 'Helper KW',
                ],
            ],
        ]);

        $data = seo_meta($page);

        $this->assertEquals('Helper Title', $data['seo_title']);
        $this->assertEquals('Helper Desc', $data['meta_description']);
        $this->assertEquals('Helper KW', $data['meta_keywords']);
    }
}
