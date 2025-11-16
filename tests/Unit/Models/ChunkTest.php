<?php

namespace Monstrex\AveSite\Tests\Unit\Models;

use Monstrex\AveSite\Models\Chunk;
use Monstrex\AveSite\Tests\TestCase;

class ChunkTest extends TestCase
{
    public function test_chunk_can_be_created(): void
    {
        $chunk = Chunk::create([
            'key' => 'test-chunk',
            'value' => 'Test value',
        ]);

        $this->assertInstanceOf(Chunk::class, $chunk);
        $this->assertEquals('test-chunk', $chunk->key);
        $this->assertEquals('Test value', $chunk->value);
    }

    public function test_by_key_scope_finds_chunk(): void
    {
        Chunk::create(['key' => 'test-1', 'value' => 'Value 1']);
        Chunk::create(['key' => 'test-2', 'value' => 'Value 2']);

        $chunk = Chunk::byKey('test-1')->first();

        $this->assertNotNull($chunk);
        $this->assertEquals('test-1', $chunk->key);
        $this->assertEquals('Value 1', $chunk->value);
    }

    public function test_chunk_key_is_unique(): void
    {
        Chunk::create(['key' => 'unique-key', 'value' => 'First']);

        $this->expectException(\Exception::class);

        Chunk::create(['key' => 'unique-key', 'value' => 'Second']);
    }
}
