<?php

namespace Monstrex\AveSite\Tests\Unit\Models;

use Monstrex\AveSite\Models\Block;
use Monstrex\AveSite\Models\BlockRegion;
use Monstrex\AveSite\Tests\TestCase;

class BlockTest extends TestCase
{
    public function test_block_can_be_created(): void
    {
        $block = Block::create([
            'title' => 'Test Block',
            'key' => 'test-block',
            'content' => '<p>Test content</p>',
            'status' => true,
        ]);

        $this->assertInstanceOf(Block::class, $block);
        $this->assertEquals('Test Block', $block->title);
        $this->assertTrue($block->status);
    }

    public function test_block_can_belong_to_region(): void
    {
        $region = BlockRegion::create(['key' => 'header', 'name' => 'Header']);

        $block = Block::create([
            'title' => 'Header Block',
            'key' => 'header-block',
            'region_id' => $region->id,
        ]);

        $this->assertEquals($region->id, $block->region_id);
        $this->assertEquals('Header', $block->region->name);
    }

    public function test_active_scope_filters_inactive_blocks(): void
    {
        Block::create(['title' => 'Active', 'key' => 'active', 'status' => true]);
        Block::create(['title' => 'Inactive', 'key' => 'inactive', 'status' => false]);

        $active = Block::active()->get();

        $this->assertCount(1, $active);
        $this->assertEquals('Active', $active->first()->title);
    }

    public function test_in_region_scope_filters_by_region(): void
    {
        $header = BlockRegion::create(['key' => 'header', 'name' => 'Header']);
        $footer = BlockRegion::create(['key' => 'footer', 'name' => 'Footer']);

        Block::create(['title' => 'Header Block', 'key' => 'header-1', 'region_id' => $header->id]);
        Block::create(['title' => 'Footer Block', 'key' => 'footer-1', 'region_id' => $footer->id]);

        $headerBlocks = Block::inRegion('header')->get();

        $this->assertCount(1, $headerBlocks);
        $this->assertEquals('Header Block', $headerBlocks->first()->title);
    }

    public function test_ordered_scope_sorts_by_order(): void
    {
        Block::create(['title' => 'Third', 'key' => 'block-3', 'order' => 3]);
        Block::create(['title' => 'First', 'key' => 'block-1', 'order' => 1]);
        Block::create(['title' => 'Second', 'key' => 'block-2', 'order' => 2]);

        $blocks = Block::ordered()->get();

        $this->assertEquals('First', $blocks->first()->title);
        $this->assertEquals('Third', $blocks->last()->title);
    }

    public function test_is_form_returns_false_for_regular_block(): void
    {
        $block = Block::create([
            'title' => 'Regular Block',
            'key' => 'regular',
            'options' => '{"data_sources":{}}',
        ]);

        $this->assertFalse($block->isForm());
    }

    public function test_is_form_returns_true_for_form_block(): void
    {
        $block = Block::create([
            'title' => 'Contact Form',
            'key' => 'contact-form',
            'options' => '{"validator":{"name":"required","email":"required|email"}}',
        ]);

        $this->assertTrue($block->isForm());
    }

    public function test_rules_field_is_cast_to_integer(): void
    {
        $block = Block::create([
            'title' => 'Test',
            'key' => 'test',
            'rules' => 1,
        ]);

        $this->assertIsInt($block->rules);
        $this->assertEquals(1, $block->rules);
    }

    public function test_elements_is_cast_to_array(): void
    {
        $block = Block::create([
            'title' => 'Test',
            'key' => 'test',
            'elements' => [
                ['image' => 'image1.jpg', 'title' => 'Element 1'],
                ['image' => 'image2.jpg', 'title' => 'Element 2']
            ],
        ]);

        $this->assertIsArray($block->elements);
        $this->assertCount(2, $block->elements);
    }

    public function test_options_accessor_formats_json(): void
    {
        $block = Block::create([
            'title' => 'Test',
            'key' => 'test',
            'options' => '{"validator":{"email":"required"}}',
        ]);

        $options = $block->options;

        $this->assertStringContainsString('"validator"', $options);
        $this->assertStringContainsString('"email"', $options);
    }
}
