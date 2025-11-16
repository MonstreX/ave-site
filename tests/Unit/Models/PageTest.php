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
            'status' => true,
        ]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('Test Page', $page->title);
        $this->assertEquals('test-page', $page->slug);
        $this->assertTrue($page->status);
    }

    public function test_page_is_root_by_default(): void
    {
        $page = Page::create([
            'title' => 'Root Page',
            'slug' => 'root',
        ]);

        $this->assertTrue($page->isRoot());
        $this->assertEquals(-1, $page->parent_id);
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

        $this->assertFalse($child->isRoot());
        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_page_can_have_children(): void
    {
        $parent = Page::create([
            'title' => 'Parent',
            'slug' => 'parent',
            'status' => true,
        ]);

        $child1 = Page::create([
            'title' => 'Child 1',
            'slug' => 'child-1',
            'parent_id' => $parent->id,
            'status' => true,
            'order' => 1,
        ]);

        $child2 = Page::create([
            'title' => 'Child 2',
            'slug' => 'child-2',
            'parent_id' => $parent->id,
            'status' => true,
            'order' => 2,
        ]);

        $this->assertCount(2, $parent->children);
        $this->assertEquals('Child 1', $parent->children->first()->title);
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
        $child = Page::create(['title' => 'Child', 'slug' => 'child', 'parent_id' => $root->id]);

        $roots = Page::roots()->get();

        $this->assertCount(1, $roots);
        $this->assertEquals('Root', $roots->first()->title);
    }

    public function test_seo_getters_return_fallback_values(): void
    {
        $page = Page::create([
            'title' => 'Test',
            'slug' => 'test',
        ]);

        $this->assertEquals('Test', $page->getSeoTitle());
        $this->assertEquals('', $page->getSeoDescription());
        $this->assertEquals('', $page->getSeoKeywords());
    }

    public function test_seo_getters_return_custom_values(): void
    {
        $page = Page::create([
            'title' => 'Test',
            'slug' => 'test',
            'seo_title' => 'Custom SEO Title',
            'seo_description' => 'Custom description',
            'seo_keywords' => 'test, keywords',
        ]);

        $this->assertEquals('Custom SEO Title', $page->getSeoTitle());
        $this->assertEquals('Custom description', $page->getSeoDescription());
        $this->assertEquals('test, keywords', $page->getSeoKeywords());
    }

    public function test_options_accessor_formats_json(): void
    {
        $page = Page::create([
            'title' => 'Test',
            'slug' => 'test',
            'options' => '{"template":"custom","data_sources":{"test":{}}}',
        ]);

        $options = $page->options;

        $this->assertStringContainsString('"template"', $options);
        $this->assertStringContainsString('"data_sources"', $options);
    }

    public function test_options_accessor_returns_empty_object_for_null(): void
    {
        $page = Page::create([
            'title' => 'Test',
            'slug' => 'test',
        ]);

        $options = $page->options;

        $this->assertEquals("{\n    \n}", $options);
    }

    public function test_media_is_cast_to_array(): void
    {
        $page = Page::create([
            'title' => 'Test',
            'slug' => 'test',
            'media' => ['image1.jpg', 'image2.jpg'],
        ]);

        $this->assertIsArray($page->media);
        $this->assertCount(2, $page->media);
    }
}
