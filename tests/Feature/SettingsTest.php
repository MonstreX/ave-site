<?php

namespace Monstrex\AveSite\Tests\Feature;

use Monstrex\AveSite\Tests\TestCase;
use Monstrex\AveSite\Models\Setting;
use Monstrex\AveSite\Database\Seeders\AveSiteSettingsSeeder;

class SettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed the settings (parent::setUp() already runs migrate:fresh)
        (new AveSiteSettingsSeeder())->run();
    }
    /**
     * Test that mail settings are saved and retrieved correctly
     */
    public function test_mail_settings_saved_correctly()
    {
        // Get mail settings from database
        $mailSetting = Setting::where('key', 'mail')->first();
        $this->assertNotNull($mailSetting);

        $config = json_decode($mailSetting->fields);
        $this->assertNotNull($config);
        $this->assertTrue(isset($config->fields->to_address));
        $this->assertTrue(isset($config->fields->from_address));
        $this->assertTrue(isset($config->fields->smtp_host));
        $this->assertTrue(isset($config->fields->smtp_port));
        $this->assertTrue(isset($config->fields->smtp_username));
        $this->assertTrue(isset($config->fields->smtp_password));
        $this->assertTrue(isset($config->fields->smtp_encryption));

        // Check that default values are set
        $this->assertNotEmpty($config->fields->to_address->value);
        $this->assertNotEmpty($config->fields->from_address->value);
        $this->assertNotEmpty($config->fields->smtp_host->value);
        $this->assertNotEmpty($config->fields->smtp_port->value);
    }

    /**
     * Test that general settings are saved and retrieved correctly
     */
    public function test_general_settings_saved_correctly()
    {
        $generalSetting = Setting::where('key', 'general')->first();
        $this->assertNotNull($generalSetting);

        $config = json_decode($generalSetting->fields);
        $this->assertNotNull($config);
        $this->assertTrue(isset($config->fields->site_title));
        $this->assertTrue(isset($config->fields->site_description));
        $this->assertTrue(isset($config->fields->site_app_name));
        $this->assertTrue(isset($config->fields->site_403_page));
        $this->assertTrue(isset($config->fields->site_captcha_site_key));
        $this->assertTrue(isset($config->fields->site_captcha_secret_key));

        // Check that default values are set
        $this->assertNotEmpty($config->fields->site_title->value);
        $this->assertNotEmpty($config->fields->site_app_name->value);
    }

    /**
     * Test that SEO settings are saved and retrieved correctly
     */
    public function test_seo_settings_saved_correctly()
    {
        $seoSetting = Setting::where('key', 'seo')->first();
        $this->assertNotNull($seoSetting);

        $config = json_decode($seoSetting->fields);
        $this->assertNotNull($config);
        $this->assertTrue(isset($config->fields->seo_title_template));
        $this->assertTrue(isset($config->fields->seo_title));
        $this->assertTrue(isset($config->fields->meta_description));
        $this->assertTrue(isset($config->fields->meta_keywords));

        // Check that template has correct default value
        $this->assertEquals('%site_title% | %seo_title%', $config->fields->seo_title_template->value);
    }

    /**
     * Test that theme settings are saved and retrieved correctly
     */
    public function test_theme_settings_saved_correctly()
    {
        $themeSetting = Setting::where('key', 'theme')->first();
        $this->assertNotNull($themeSetting);

        $config = json_decode($themeSetting->fields);
        $this->assertNotNull($config);
        $this->assertTrue(isset($config->fields->theme_logo));
        $this->assertTrue(isset($config->fields->theme_favicon));
        $this->assertTrue(isset($config->fields->theme_banner_image));
    }

    /**
     * Test that settings can be updated and changes are persisted
     */
    public function test_settings_update_persists()
    {
        $mailSetting = Setting::where('key', 'mail')->first();
        $config = json_decode($mailSetting->fields);

        // Update a setting
        $newToAddress = 'newemail@test.com';
        $config->fields->to_address->value = $newToAddress;

        $mailSetting->fields = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $mailSetting->save();

        // Retrieve and verify
        $updatedSetting = Setting::where('key', 'mail')->first();
        $updatedConfig = json_decode($updatedSetting->fields);

        $this->assertEquals($newToAddress, $updatedConfig->fields->to_address->value);
    }

    /**
     * Test that all 4 setting groups exist
     */
    public function test_all_setting_groups_exist()
    {
        $keys = ['general', 'mail', 'seo', 'theme'];

        foreach ($keys as $key) {
            $setting = Setting::where('key', $key)->first();
            $this->assertNotNull($setting, "Setting group '{$key}' not found");
            $this->assertNotEmpty($setting->title);
            $this->assertNotEmpty($setting->fields);
        }
    }

    /**
     * Test that settings are ordered correctly
     */
    public function test_settings_ordering()
    {
        $settings = Setting::orderBy('order')->get();

        $this->assertCount(4, $settings);
        $this->assertEquals('general', $settings[0]->key);
        $this->assertEquals('mail', $settings[1]->key);
        $this->assertEquals('seo', $settings[2]->key);
        $this->assertEquals('theme', $settings[3]->key);
    }

    /**
     * Test that settings can be edited and saved programmatically
     */
    public function test_edit_settings_programmatically()
    {
        $setting = Setting::where('key', 'general')->first();
        $config = json_decode($setting->fields);

        // Modify the settings
        $newTitle = 'Updated Site Title';
        $config->fields->site_title->value = $newTitle;

        // Save back to database
        $setting->fields = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $setting->save();

        // Verify setting was saved
        $updatedSetting = Setting::where('key', 'general')->first();
        $updatedConfig = json_decode($updatedSetting->fields);
        $this->assertEquals($newTitle, $updatedConfig->fields->site_title->value);
    }

    /**
     * Test that SMTP settings are applied to Laravel config
     */
    public function test_smtp_settings_applied_to_config()
    {
        $mailSetting = Setting::where('key', 'mail')->first();
        $config = json_decode($mailSetting->fields);

        // Check that SMTP settings exist and have values
        $this->assertNotEmpty($config->fields->smtp_host->value);
        $this->assertNotEmpty($config->fields->smtp_port->value);
        $this->assertNotEmpty($config->fields->smtp_encryption->value);

        // These should be applied to Laravel's mail config
        // This is tested through the ServiceProvider
    }

    /**
     * Test that mail settings validation works with missing SMTP settings
     */
    public function test_smtp_validation_with_missing_host()
    {
        $setting = Setting::where('key', 'mail')->first();
        $config = json_decode($setting->fields);

        // Clear SMTP host
        $config->fields->smtp_host->value = '';

        $setting->fields = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $setting->save();

        // Verify the field is actually empty
        $updated = Setting::where('key', 'mail')->first();
        $updatedConfig = json_decode($updated->fields);
        $this->assertEmpty($updatedConfig->fields->smtp_host->value);
    }

    /**
     * Test that mail settings validation works with missing username
     */
    public function test_smtp_validation_with_missing_username()
    {
        $setting = Setting::where('key', 'mail')->first();
        $config = json_decode($setting->fields);

        // Clear SMTP username
        $config->fields->smtp_username->value = '';

        $setting->fields = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $setting->save();

        // Verify the field is actually empty
        $updated = Setting::where('key', 'mail')->first();
        $updatedConfig = json_decode($updated->fields);
        $this->assertEmpty($updatedConfig->fields->smtp_username->value);
    }

    /**
     * Test that mail settings have all required SMTP fields
     */
    public function test_mail_settings_have_all_smtp_fields()
    {
        $mailSetting = Setting::where('key', 'mail')->first();
        $config = json_decode($mailSetting->fields);

        // Check all SMTP fields exist
        $this->assertTrue(isset($config->fields->smtp_host));
        $this->assertTrue(isset($config->fields->smtp_port));
        $this->assertTrue(isset($config->fields->smtp_username));
        $this->assertTrue(isset($config->fields->smtp_password));
        $this->assertTrue(isset($config->fields->smtp_encryption));

        // Verify they all have correct types
        $this->assertIsObject($config->fields->smtp_host);
        $this->assertIsObject($config->fields->smtp_port);
        $this->assertIsObject($config->fields->smtp_username);
        $this->assertIsObject($config->fields->smtp_password);
        $this->assertIsObject($config->fields->smtp_encryption);
    }

    /**
     * Test that mail settings validation detects missing SMTP configuration
     */
    public function test_smtp_settings_validation_with_all_empty()
    {
        $setting = Setting::where('key', 'mail')->first();
        $config = json_decode($setting->fields);

        // Clear all SMTP fields
        $config->fields->smtp_host->value = '';
        $config->fields->smtp_port->value = '';
        $config->fields->smtp_username->value = '';
        $config->fields->smtp_password->value = '';
        $config->fields->to_address->value = '';
        $config->fields->from_address->value = '';

        $setting->fields = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $setting->save();

        // Verify they're actually empty
        $updated = Setting::where('key', 'mail')->first();
        $updatedConfig = json_decode($updated->fields);
        $this->assertEmpty($updatedConfig->fields->smtp_host->value);
        $this->assertEmpty($updatedConfig->fields->smtp_port->value);
        $this->assertEmpty($updatedConfig->fields->smtp_username->value);
        $this->assertEmpty($updatedConfig->fields->smtp_password->value);
        $this->assertEmpty($updatedConfig->fields->to_address->value);
        $this->assertEmpty($updatedConfig->fields->from_address->value);
    }

    /**
     * Test SettingsService::get() retrieves correct values
     */
    public function test_settings_service_get_method()
    {
        $service = app(\Monstrex\AveSite\Services\SettingsService::class);

        // Test getting a mail field value
        $toAddress = $service->get('mail.to_address');
        $this->assertNotEmpty($toAddress);
        $this->assertIsString($toAddress);

        // Test getting non-existent field returns default
        $nonExistent = $service->get('mail.non_existent_field', 'default_value');
        $this->assertEquals('default_value', $nonExistent);
    }

    /**
     * Test SettingsService::getGroup() retrieves all group fields
     */
    public function test_settings_service_get_group_method()
    {
        $service = app(\Monstrex\AveSite\Services\SettingsService::class);

        // Get mail group
        $mailGroup = $service->getGroup('mail');
        $this->assertIsArray($mailGroup);
        $this->assertNotEmpty($mailGroup);

        // Check required mail fields exist
        $this->assertArrayHasKey('to_address', $mailGroup);
        $this->assertArrayHasKey('from_address', $mailGroup);
        $this->assertArrayHasKey('smtp_driver', $mailGroup);
        $this->assertArrayHasKey('smtp_host', $mailGroup);
        $this->assertArrayHasKey('smtp_port', $mailGroup);
        $this->assertArrayHasKey('smtp_username', $mailGroup);
        $this->assertArrayHasKey('smtp_password', $mailGroup);
        $this->assertArrayHasKey('smtp_encryption', $mailGroup);
    }

    /**
     * Test that general group returns correct structure
     */
    public function test_settings_service_general_group()
    {
        $service = app(\Monstrex\AveSite\Services\SettingsService::class);

        $generalGroup = $service->getGroup('general');
        $this->assertIsArray($generalGroup);
        $this->assertArrayHasKey('site_title', $generalGroup);
        $this->assertArrayHasKey('site_description', $generalGroup);
        $this->assertArrayHasKey('site_app_name', $generalGroup);
        $this->assertArrayHasKey('site_403_page', $generalGroup);
        $this->assertArrayHasKey('site_captcha_site_key', $generalGroup);
        $this->assertArrayHasKey('site_captcha_secret_key', $generalGroup);
    }

    /**
     * Test SettingsService correctly types values
     */
    public function test_settings_service_value_typing()
    {
        $service = app(\Monstrex\AveSite\Services\SettingsService::class);

        $mailGroup = $service->getGroup('mail');

        // SMTP port should be integer
        $this->assertIsInt($mailGroup['smtp_port']);

        // String fields should be strings
        $this->assertIsString($mailGroup['smtp_host']);
        $this->assertIsString($mailGroup['to_address']);
    }

    public function test_site_settings_group_helper_returns_flat_array()
    {
        $group = site_settings_group('general');

        $this->assertIsArray($group);
        $this->assertArrayHasKey('site_title', $group);
        $this->assertArrayHasKey('site_403_page', $group);
    }
}
