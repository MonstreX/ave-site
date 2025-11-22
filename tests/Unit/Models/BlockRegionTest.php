<?php

namespace Monstrex\AveSite\Tests\Unit\Models;

use Monstrex\AveSite\Models\Block;
use Monstrex\AveSite\Models\BlockRegion;
use Monstrex\AveSite\Tests\TestCase;

class BlockRegionTest extends TestCase
{
    public function test_block_region_can_be_created(): void
    {
        $region = BlockRegion::create([
            'key' => 'header',
            'title' => 'Header Region',
        ]);

        $this->assertInstanceOf(BlockRegion::class, $region);
        $this->assertEquals('header', $region->key);
        $this->assertEquals('Header Region', $region->title);
    }

    public function test_block_region_can_have_blocks(): void
    {
        $region = BlockRegion::create(['key' => 'sidebar', 'title' => 'Sidebar']);

        Block::create(['title' => 'Block 1', 'key' => 'block-1', 'region_id' => $region->id]);
        Block::create(['title' => 'Block 2', 'key' => 'block-2', 'region_id' => $region->id]);

        $this->assertCount(2, $region->blocks);
    }

    public function test_block_region_key_is_unique(): void
    {
        BlockRegion::create(['key' => 'footer', 'title' => 'Footer']);

        $this->expectException(\Exception::class);

        BlockRegion::create(['key' => 'footer', 'title' => 'Another Footer']);
    }
}
