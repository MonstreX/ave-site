<?php

namespace Monstrex\AveSite\Tests\Feature;

use Monstrex\AveSite\Models\Page;
use Monstrex\AveSite\Services\DataService;
use Monstrex\AveSite\Tests\TestCase;

class MenuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test pages with hierarchy
        $this->createTestPages();
    }

    protected function createTestPages(): void
    {
        // Root pages
        Page::create([
            'title' => 'Home',
            'slug' => 'home',
            'parent_id' => null,
            'order' => 1,
            'status' => 1,
            'content' => 'Home page',
        ]);

        $about = Page::create([
            'title' => 'About',
            'slug' => 'about',
            'parent_id' => null,
            'order' => 2,
            'status' => 1,
            'content' => 'About page',
        ]);

        $services = Page::create([
            'title' => 'Services',
            'slug' => 'services',
            'parent_id' => null,
            'order' => 3,
            'status' => 1,
            'content' => 'Services page',
        ]);

        // Child pages
        Page::create([
            'title' => 'Web Design',
            'slug' => 'web-design',
            'parent_id' => $services->id,
            'order' => 1,
            'status' => 1,
            'content' => 'Web design service',
        ]);

        Page::create([
            'title' => 'Consulting',
            'slug' => 'consulting',
            'parent_id' => $services->id,
            'order' => 2,
            'status' => 1,
            'content' => 'Consulting service',
        ]);
    }

    public function test_get_menu_returns_hierarchical_structure(): void
    {
        $dataService = app(DataService::class);
        $menu = $dataService->getMenu();

        $this->assertNotNull($menu);
        $this->assertIsArray($menu);
        $this->assertCount(3, $menu); // 3 root pages

        // Check first item
        $this->assertEquals('Home', $menu[0]['title']);
        $this->assertEquals('home', $menu[0]['slug']);

        // Check Services has children
        $services = collect($menu)->firstWhere('slug', 'services');
        $this->assertNotNull($services);
        $this->assertArrayHasKey('children', $services);
        $this->assertCount(2, $services['children']);
        $this->assertEquals('Web Design', $services['children'][0]['title']);
        $this->assertEquals('Consulting', $services['children'][1]['title']);
    }

    public function test_get_menu_helper_works(): void
    {
        $menu = get_menu();

        $this->assertNotNull($menu);
        $this->assertIsArray($menu);
        $this->assertCount(3, $menu);
    }

    public function test_render_menu_helper_returns_html(): void
    {
        $html = render_menu();

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('<ul', $html);
        $this->assertStringContainsString('<li', $html);
        $this->assertStringContainsString('Home', $html);
        $this->assertStringContainsString('About', $html);
        $this->assertStringContainsString('Services', $html);
    }

    public function test_render_menu_simple_renders_hierarchy(): void
    {
        $menu = get_menu();
        $html = render_menu_simple($menu);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('menu-level-0', $html);
        $this->assertStringContainsString('menu-level-1', $html);
        $this->assertStringContainsString('Web Design', $html);
        $this->assertStringContainsString('Consulting', $html);
    }

    public function test_menu_respects_parent_filter(): void
    {
        $dataService = app(DataService::class);
        $services = Page::where('slug', 'services')->first();

        $menu = $dataService->getMenu(null, [
            'field' => 'id',
            'value' => $services->id,
        ]);

        $this->assertNotNull($menu);
        $this->assertIsArray($menu);
        $this->assertCount(1, $menu);
        $this->assertEquals('Services', $menu[0]['title']);
        $this->assertArrayHasKey('children', $menu[0]);
        $this->assertCount(2, $menu[0]['children']);
    }

    public function test_flat_to_tree_helper_works(): void
    {
        $flat = [
            ['id' => 1, 'title' => 'Root 1', 'parent_id' => null],
            ['id' => 2, 'title' => 'Child 1', 'parent_id' => 1],
            ['id' => 3, 'title' => 'Child 2', 'parent_id' => 1],
            ['id' => 4, 'title' => 'Root 2', 'parent_id' => null],
        ];

        $tree = flat_to_tree($flat);

        $this->assertCount(2, $tree); // 2 root items
        $this->assertArrayHasKey('children', $tree[0]);
        $this->assertCount(2, $tree[0]['children']);
    }

    public function test_menu_returns_null_for_non_tree_model(): void
    {
        $dataService = app(DataService::class);

        // Use a model without parent_id column
        $menu = $dataService->getMenu('non_existent_table');

        $this->assertNull($menu);
    }
}
