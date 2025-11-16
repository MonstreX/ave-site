<?php

namespace Monstrex\AveSite\Tests\Unit\Models;

use Monstrex\AveSite\Models\Setting;
use Monstrex\AveSite\Tests\TestCase;

class SettingTest extends TestCase
{
    public function test_setting_can_be_created(): void
    {
        $setting = Setting::create([
            'key' => 'general',
            'group' => 'general',
            'fields' => json_encode([
                'fields' => [
                    'site_title' => [
                        'type' => 'text',
                        'value' => 'My Site',
                    ],
                ],
            ]),
        ]);

        $this->assertInstanceOf(Setting::class, $setting);
        $this->assertEquals('general', $setting->key);
    }

    public function test_by_key_scope_finds_setting(): void
    {
        Setting::create(['key' => 'general', 'group' => 'general', 'fields' => '{}']);
        Setting::create(['key' => 'mail', 'group' => 'mail', 'fields' => '{}']);

        $setting = Setting::byKey('general')->first();

        $this->assertNotNull($setting);
        $this->assertEquals('general', $setting->key);
    }

    public function test_by_group_scope_finds_setting(): void
    {
        Setting::create(['key' => 'general-settings', 'group' => 'general', 'fields' => '{}']);
        Setting::create(['key' => 'mail-settings', 'group' => 'mail', 'fields' => '{}']);

        $setting = Setting::byGroup('mail')->first();

        $this->assertNotNull($setting);
        $this->assertEquals('mail', $setting->group);
    }

    public function test_get_fields_array_returns_empty_for_no_fields(): void
    {
        $setting = Setting::create([
            'key' => 'test',
            'group' => 'test',
            'fields' => '{}',
        ]);

        $fields = $setting->getFieldsArray();

        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
    }

    public function test_get_fields_array_returns_field_values(): void
    {
        $setting = Setting::create([
            'key' => 'general',
            'group' => 'general',
            'fields' => json_encode([
                'fields' => [
                    'site_title' => [
                        'type' => 'text',
                        'value' => 'My Site',
                    ],
                    'site_tagline' => [
                        'type' => 'text',
                        'value' => 'Just another site',
                    ],
                ],
            ]),
        ]);

        $fields = $setting->getFieldsArray();

        $this->assertArrayHasKey('site_title', $fields);
        $this->assertEquals('My Site', $fields['site_title']);
        $this->assertEquals('Just another site', $fields['site_tagline']);
    }

    public function test_get_fields_array_skips_sections(): void
    {
        $setting = Setting::create([
            'key' => 'general',
            'group' => 'general',
            'fields' => json_encode([
                'fields' => [
                    'section_mail' => [
                        'type' => 'section',
                        'label' => 'Mail Settings',
                    ],
                    'smtp_host' => [
                        'type' => 'text',
                        'value' => 'smtp.gmail.com',
                    ],
                ],
            ]),
        ]);

        $fields = $setting->getFieldsArray();

        $this->assertArrayNotHasKey('section_mail', $fields);
        $this->assertArrayHasKey('smtp_host', $fields);
    }

    public function test_get_fields_array_casts_checkbox_to_bool(): void
    {
        $setting = Setting::create([
            'key' => 'general',
            'group' => 'general',
            'fields' => json_encode([
                'fields' => [
                    'debug_mode' => [
                        'type' => 'checkbox',
                        'value' => true,
                    ],
                    'maintenance' => [
                        'type' => 'checkbox',
                        'value' => false,
                    ],
                ],
            ]),
        ]);

        $fields = $setting->getFieldsArray();

        $this->assertIsBool($fields['debug_mode']);
        $this->assertTrue($fields['debug_mode']);
        $this->assertFalse($fields['maintenance']);
    }

    public function test_get_fields_array_casts_number_to_int(): void
    {
        $setting = Setting::create([
            'key' => 'general',
            'group' => 'general',
            'fields' => json_encode([
                'fields' => [
                    'items_per_page' => [
                        'type' => 'number',
                        'value' => '10',
                    ],
                    'timeout' => [
                        'type' => 'number',
                        'value' => 30,
                    ],
                ],
            ]),
        ]);

        $fields = $setting->getFieldsArray();

        $this->assertIsInt($fields['items_per_page']);
        $this->assertEquals(10, $fields['items_per_page']);
        $this->assertEquals(30, $fields['timeout']);
    }
}
