<?php

namespace Monstrex\AveSite\Tests\Feature;

use Illuminate\Support\Facades\Notification;
use Monstrex\AveSite\Database\Seeders\AveSiteSettingsSeeder;
use Monstrex\AveSite\Models\Form;
use Monstrex\AveSite\Notifications\SendFormNotification;
use Monstrex\AveSite\Tests\TestCase;

class FormSubmissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (new AveSiteSettingsSeeder())->run();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    protected function createForm(array $overrides = []): Form
    {
        return Form::create(array_merge([
            'status' => true,
            'order' => 1,
            'title' => 'Contact Form',
            'key' => 'contact-form',
            'content' => '<form method="POST" action="/api/send-form"><input type="hidden" name="_form_alias" value="{{ form_alias }}"><input type="text" name="name"></form>',
            'details' => json_encode([
                'validator' => [
                    'name' => 'required',
                ],
                'messages' => [
                    'name.required' => 'Name is required',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        ], $overrides));
    }

    public function test_form_submission_sends_notification()
    {
        Notification::fake();
        $form = $this->createForm();

        $response = $this->post('/api/send-form', [
            '_form_alias' => $form->key,
            'name' => 'John Doe',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('form_status', 'success');

        Notification::assertSentOnDemand(SendFormNotification::class, function ($notification, $channels, $notifiable) {
            return in_array('mail', $channels, true);
        });
    }

    public function test_form_submission_returns_validation_errors_for_ajax_requests()
    {
        $form = $this->createForm();

        $response = $this->postJson('/api/send-form', [
            '_form_alias' => $form->key,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(422);
        $response->assertJson(['type' => 'error-validation']);
    }
}
