<?php

namespace Monstrex\AveSite\Database\Seeders;

use Illuminate\Database\Seeder;
use Monstrex\AveSite\Models\Setting;

class AveSiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * GENERAL SETTINGS
         */
        $generalFields = [
            'fields' => [
                'section_main' => [
                    'type' => 'section',
                    'icon' => 'voyager-tools',
                    'label' => 'General Settings',
                ],
                'site_title' => [
                    'label' => 'Site Title',
                    'type' => 'text',
                    'value' => 'My Website',
                    'class' => 'col-md-12',
                ],
                'site_description' => [
                    'label' => 'Site Description',
                    'type' => 'text',
                    'value' => 'Website description',
                    'class' => 'col-md-12',
                ],
                'section_pages' => [
                    'type' => 'section',
                    'icon' => 'voyager-documentation',
                    'label' => 'Pages Settings',
                ],
                'site_home_page' => [
                    'label' => 'Home Page ID',
                    'type' => 'number',
                    'value' => '1',
                    'class' => 'col-md-12',
                ],
                'site_404_page' => [
                    'label' => '404 Page ID',
                    'type' => 'number',
                    'value' => '2',
                    'class' => 'col-md-12',
                ],
                'section_system' => [
                    'type' => 'section',
                    'icon' => 'voyager-exclamation',
                    'label' => 'System Settings',
                ],
                'site_app_name' => [
                    'label' => 'Application Name',
                    'type' => 'text',
                    'value' => 'My App',
                    'class' => 'col-md-12',
                ],
                'debug_mode' => [
                    'label' => 'Debug Mode',
                    'type' => 'checkbox',
                    'value' => '0',
                    'class' => 'col-md-12',
                ],
            ],
        ];

        Setting::firstOrCreate([
            'key' => 'general',
            'group' => 'general',
        ], [
            'title' => 'General',
            'order' => 1,
            'fields' => json_encode($generalFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        /**
         * MAIL SETTINGS
         */
        $mailFields = [
            'fields' => [
                'to_address' => [
                    'label' => 'Email Recipient',
                    'type' => 'text',
                    'value' => 'admin@example.com',
                    'class' => 'col-md-12',
                ],
                'from_name' => [
                    'label' => 'From Name',
                    'type' => 'text',
                    'value' => 'My Website',
                    'class' => 'col-md-12',
                ],
                'from_address' => [
                    'label' => 'From Email',
                    'type' => 'text',
                    'value' => 'noreply@example.com',
                    'class' => 'col-md-12',
                ],
                'section_smtp' => [
                    'type' => 'section',
                    'icon' => 'voyager-mail',
                    'label' => 'SMTP Configuration',
                ],
                'smtp_driver' => [
                    'label' => 'Mail Driver',
                    'type' => 'dropdown',
                    'value' => 'smtp',
                    'options' => [
                        'smtp' => 'SMTP',
                        'mailgun' => 'Mailgun',
                        'log' => 'Log',
                    ],
                    'class' => 'col-md-12',
                ],
                'smtp_host' => [
                    'label' => 'SMTP Host',
                    'type' => 'text',
                    'value' => 'smtp.mailtrap.io',
                    'class' => 'col-md-12',
                ],
                'smtp_port' => [
                    'label' => 'SMTP Port',
                    'type' => 'number',
                    'value' => '2525',
                    'class' => 'col-md-12',
                ],
                'smtp_username' => [
                    'label' => 'SMTP Username',
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'smtp_password' => [
                    'label' => 'SMTP Password',
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'smtp_encryption' => [
                    'label' => 'SMTP Encryption',
                    'type' => 'radio',
                    'value' => 'tls',
                    'options' => [
                        'none' => 'None',
                        'ssl' => 'SSL',
                        'tls' => 'TLS',
                    ],
                    'class' => 'col-md-12',
                ],
                'test_mail' => [
                    'label' => 'Send Test Email',
                    'type' => 'route',
                    'value' => 'ave-site.settings.test-mail',
                    'icon' => 'voyager-mail',
                    'class' => 'col-md-12',
                ],
            ],
        ];

        Setting::firstOrCreate([
            'key' => 'mail',
            'group' => 'mail',
        ], [
            'title' => 'Mail',
            'order' => 2,
            'fields' => json_encode($mailFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        /**
         * SEO SETTINGS
         */
        $seoFields = [
            'fields' => [
                'seo_title_template' => [
                    'label' => 'Title Template',
                    'type' => 'text',
                    'value' => '%site_title% | %seo_title%',
                    'class' => 'col-md-12',
                ],
                'seo_title' => [
                    'label' => 'Default SEO Title',
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'meta_description' => [
                    'label' => 'Meta Description',
                    'type' => 'textarea',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'meta_keywords' => [
                    'label' => 'Meta Keywords',
                    'type' => 'textarea',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
            ],
        ];

        Setting::firstOrCreate([
            'key' => 'seo',
            'group' => 'seo',
        ], [
            'title' => 'SEO',
            'order' => 3,
            'fields' => json_encode($seoFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        /**
         * THEME SETTINGS
         */
        $themeFields = [
            'fields' => [
                'theme_logo' => [
                    'label' => 'Logo',
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'theme_favicon' => [
                    'label' => 'Favicon',
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'theme_banner_image' => [
                    'label' => 'Banner Image',
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
            ],
        ];

        Setting::firstOrCreate([
            'key' => 'theme',
            'group' => 'theme',
        ], [
            'title' => 'Theme',
            'order' => 4,
            'fields' => json_encode($themeFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
