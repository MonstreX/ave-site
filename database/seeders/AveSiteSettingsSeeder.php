<?php

namespace Monstrex\AveSite\Database\Seeders;

use Illuminate\Database\Seeder;
use Monstrex\AveSite\Models\Setting;

class AveSiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $t = 'ave-site::seeders.settings';

        /**
         * GENERAL SETTINGS
         */
        $generalFields = [
            'fields' => [
                'section_main' => [
                    'type' => 'section',
                    'icon' => 'voyager-tools',
                    'label' => trans("$t.general.section_main"),
                ],
                'site_title' => [
                    'label' => trans("$t.general.site_title"),
                    'type' => 'text',
                    'value' => trans("$t.general.site_title_value"),
                    'class' => 'col-md-12',
                ],
                'site_description' => [
                    'label' => trans("$t.general.site_description"),
                    'type' => 'text',
                    'value' => trans("$t.general.site_description_value"),
                    'class' => 'col-md-12',
                ],
                'section_pages' => [
                    'type' => 'section',
                    'icon' => 'voyager-documentation',
                    'label' => trans("$t.general.section_pages"),
                ],
                'site_home_page' => [
                    'label' => trans("$t.general.site_home_page"),
                    'type' => 'number',
                    'value' => '1',
                    'class' => 'col-md-12',
                ],
                'site_404_page' => [
                    'label' => trans("$t.general.site_404_page"),
                    'type' => 'number',
                    'value' => '2',
                    'class' => 'col-md-12',
                ],
                'section_system' => [
                    'type' => 'section',
                    'icon' => 'voyager-exclamation',
                    'label' => trans("$t.general.section_system"),
                ],
                'site_app_name' => [
                    'label' => trans("$t.general.site_app_name"),
                    'type' => 'text',
                    'value' => trans("$t.general.site_app_name_value"),
                    'class' => 'col-md-12',
                ],
                'debug_mode' => [
                    'label' => trans("$t.general.debug_mode"),
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
            'title' => trans("$t.general.title"),
            'order' => 1,
            'fields' => json_encode($generalFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        /**
         * MAIL SETTINGS
         */
        $mailFields = [
            'fields' => [
                'to_address' => [
                    'label' => trans("$t.mail.to_address"),
                    'type' => 'text',
                    'value' => trans("$t.mail.to_address_value"),
                    'class' => 'col-md-12',
                ],
                'from_name' => [
                    'label' => trans("$t.mail.from_name"),
                    'type' => 'text',
                    'value' => trans("$t.mail.from_name_value"),
                    'class' => 'col-md-12',
                ],
                'from_address' => [
                    'label' => trans("$t.mail.from_address"),
                    'type' => 'text',
                    'value' => trans("$t.mail.from_address_value"),
                    'class' => 'col-md-12',
                ],
                'section_smtp' => [
                    'type' => 'section',
                    'icon' => 'voyager-mail',
                    'label' => trans("$t.mail.section_smtp"),
                ],
                'smtp_driver' => [
                    'label' => trans("$t.mail.smtp_driver"),
                    'type' => 'dropdown',
                    'value' => 'smtp',
                    'options' => [
                        'smtp' => trans("$t.mail.smtp_driver_option_smtp"),
                        'mailgun' => trans("$t.mail.smtp_driver_option_mailgun"),
                        'log' => trans("$t.mail.smtp_driver_option_log"),
                    ],
                    'class' => 'col-md-12',
                ],
                'smtp_host' => [
                    'label' => trans("$t.mail.smtp_host"),
                    'type' => 'text',
                    'value' => trans("$t.mail.smtp_host_value"),
                    'class' => 'col-md-12',
                ],
                'smtp_port' => [
                    'label' => trans("$t.mail.smtp_port"),
                    'type' => 'number',
                    'value' => trans("$t.mail.smtp_port_value"),
                    'class' => 'col-md-12',
                ],
                'smtp_username' => [
                    'label' => trans("$t.mail.smtp_username"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'smtp_password' => [
                    'label' => trans("$t.mail.smtp_password"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'smtp_encryption' => [
                    'label' => trans("$t.mail.smtp_encryption"),
                    'type' => 'radio',
                    'value' => 'tls',
                    'options' => [
                        'none' => trans("$t.mail.smtp_encryption_option_none"),
                        'ssl' => trans("$t.mail.smtp_encryption_option_ssl"),
                        'tls' => trans("$t.mail.smtp_encryption_option_tls"),
                    ],
                    'class' => 'col-md-12',
                ],
                'test_mail' => [
                    'label' => trans("$t.mail.test_mail"),
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
            'title' => trans("$t.mail.title"),
            'order' => 2,
            'fields' => json_encode($mailFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        /**
         * SEO SETTINGS
         */
        $seoFields = [
            'fields' => [
                'seo_title_template' => [
                    'label' => trans("$t.seo.seo_title_template"),
                    'type' => 'text',
                    'value' => trans("$t.seo.seo_title_template_value"),
                    'class' => 'col-md-12',
                ],
                'seo_title' => [
                    'label' => trans("$t.seo.seo_title"),
                    'type' => 'text',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'meta_description' => [
                    'label' => trans("$t.seo.meta_description"),
                    'type' => 'textarea',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'meta_keywords' => [
                    'label' => trans("$t.seo.meta_keywords"),
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
            'title' => trans("$t.seo.title"),
            'order' => 3,
            'fields' => json_encode($seoFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);

        /**
         * THEME SETTINGS
         */
        $themeFields = [
            'fields' => [
                'theme_logo' => [
                    'label' => trans("$t.theme.theme_logo"),
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'theme_favicon' => [
                    'label' => trans("$t.theme.theme_favicon"),
                    'type' => 'media',
                    'value' => '',
                    'class' => 'col-md-12',
                ],
                'theme_banner_image' => [
                    'label' => trans("$t.theme.theme_banner_image"),
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
            'title' => trans("$t.theme.title"),
            'order' => 4,
            'fields' => json_encode($themeFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
