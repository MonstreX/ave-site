# Forms System

Complete guide to creating, rendering, and handling forms in Ave Site.

## Overview

Ave Site provides a complete form handling system with:

- Liquid template-based form rendering
- Laravel validation integration
- Email notifications on submission
- File attachment support
- Google reCAPTCHA v3 integration
- AJAX and traditional form submission

## Creating Forms

### Via Admin Panel

1. Go to **Content > Forms** in Ave Admin Panel
2. Click **Create**
3. Fill in form details:
   - **Title** - Display name
   - **Key** - Unique identifier (e.g., `contact-form`)
   - **Status** - Active/Inactive
   - **Content** - Liquid template for form HTML
   - **Details** - JSON configuration

### Form Configuration (Details JSON)

```json
{
    "validator": {
        "name": "required|string|max:255",
        "email": "required|email",
        "phone": "nullable|string|max:20",
        "message": "required|string|min:10|max:5000"
    },
    "messages": {
        "name.required": "Please enter your name",
        "email.required": "Email address is required",
        "email.email": "Please enter a valid email address",
        "message.required": "Please enter your message",
        "message.min": "Message must be at least 10 characters"
    },
    "to_address": "contact@example.com"
}
```

**Configuration Fields:**

| Field | Description |
|-------|-------------|
| `validator` | Laravel validation rules |
| `messages` | Custom validation messages |
| `to_address` | Email recipient address |

---

## Form Template

### Basic Contact Form

```liquid
<form action="/api/send-form" method="POST" class="contact-form">
    @csrf
    <input type="hidden" name="_form_key" value="{{ _form_key }}">

    {% if _subject %}
        <input type="hidden" name="_subject" value="{{ _subject }}">
    {% endif %}

    {% if _suffix %}
        <input type="hidden" name="_suffix" value="{{ _suffix }}">
    {% endif %}

    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" id="name" required>
        {% if errors.name %}
            <span class="error">{{ errors.name[0] }}</span>
        {% endif %}
    </div>

    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" name="email" id="email" required>
        {% if errors.email %}
            <span class="error">{{ errors.email[0] }}</span>
        {% endif %}
    </div>

    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="tel" name="phone" id="phone">
    </div>

    <div class="form-group">
        <label for="message">Message *</label>
        <textarea name="message" id="message" rows="5" required></textarea>
        {% if errors.message %}
            <span class="error">{{ errors.message[0] }}</span>
        {% endif %}
    </div>

    <button type="submit">Send Message</button>
</form>
```

### Available Template Variables

| Variable | Description |
|----------|-------------|
| `_form_key` | Form identifier |
| `_subject` | Email subject (if passed) |
| `_suffix` | Subject suffix (if passed) |
| `errors` | Validation errors from session |

---

## Rendering Forms

### Using Helper Function

```php
// Simple
echo render_form('contact-form');

// With subject
echo render_form('contact-form', 'Contact Request');

// With subject and suffix
echo render_form('contact-form', 'Contact Request', 'From Website');
```

### In Blade Templates

```blade
{{-- Using helper --}}
{!! render_form('contact-form') !!}
{!! render_form('contact-form', 'Contact Request', 'Website') !!}

{{-- Using directive --}}
@renderForm('contact-form')
@renderForm('contact-form', 'Contact Request', 'Website')
```

### In Liquid Templates

```liquid
{{ 'contact-form' | form }}
{{ 'contact-form' | form: 'Contact Request', 'Website' }}
```

### Using Shortcode

```html
[form name="contact-form"]
[form name="contact-form" subject="Contact Request" suffix="Website"]
```

---

## Form Submission

### Endpoint

Forms submit to: `POST /api/send-form`

### Request Fields

| Field | Required | Description |
|-------|----------|-------------|
| `_token` | Yes | CSRF token |
| `_form_key` | Yes* | Form identifier |
| `_form_id` | Yes* | Alternative: Form ID |
| `_subject` | No | Email subject |
| `_suffix` | No | Subject suffix |
| Form fields | Varies | User input data |

*Either `_form_key` or `_form_id` is required.

### Response Formats

**AJAX Request (JSON):**

Success (200):
```json
{
    "type": "success",
    "messages": ["Thank you! Your message has been sent."]
}
```

Validation Error (422):
```json
{
    "type": "error",
    "messages": {
        "email": ["The email field is required."],
        "message": ["The message must be at least 10 characters."]
    }
}
```

**Traditional Request (Redirect):**

Redirects back with session flash data:
- `form_status` - 'success' or 'error'
- `form_messages` - Array of messages

---

## AJAX Form Handling

### JavaScript Example

```javascript
document.querySelector('.contact-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');

    // Disable button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    // Clear previous errors
    form.querySelectorAll('.error').forEach(el => el.remove());

    try {
        const response = await fetch('/api/send-form', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            // Success
            form.reset();
            showMessage('success', data.messages[0]);
        } else {
            // Validation errors
            if (typeof data.messages === 'object') {
                Object.keys(data.messages).forEach(field => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        const error = document.createElement('span');
                        error.className = 'error';
                        error.textContent = data.messages[field][0];
                        input.parentNode.appendChild(error);
                    }
                });
            }
        }
    } catch (error) {
        showMessage('error', 'An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Message';
    }
});

function showMessage(type, message) {
    // Implement your notification UI
    alert(message);
}
```

---

## Validation Rules

### Standard Laravel Rules

```json
{
    "validator": {
        "name": "required|string|min:2|max:255",
        "email": "required|email:rfc,dns",
        "phone": "nullable|regex:/^[0-9+\\-\\s()]+$/|max:20",
        "company": "nullable|string|max:255",
        "subject": "required|string|max:255",
        "message": "required|string|min:10|max:5000",
        "terms": "accepted"
    }
}
```

### Common Validation Rules

| Rule | Description |
|------|-------------|
| `required` | Field must be present and not empty |
| `email` | Must be valid email format |
| `string` | Must be a string |
| `min:n` | Minimum length (string) or value (number) |
| `max:n` | Maximum length or value |
| `numeric` | Must be numeric |
| `regex:/pattern/` | Must match regex pattern |
| `accepted` | Must be "yes", "on", "1", or "true" |
| `nullable` | Field can be null/empty |
| `in:a,b,c` | Value must be in list |

---

## reCAPTCHA Integration

### Setup

1. Get reCAPTCHA v3 keys from [Google](https://www.google.com/recaptcha/admin)

2. Configure in Site Settings:
   - Go to **Site Settings > General**
   - Set `site_captcha_site_key` - Public site key
   - Set `site_captcha_secret_key` - Secret key

3. Add to form template:

```liquid
<form action="/api/send-form" method="POST" class="contact-form" id="contact-form">
    @csrf
    <input type="hidden" name="_form_key" value="{{ _form_key }}">
    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

    {{-- Form fields --}}

    <button type="submit">Send</button>
</form>

<script src="https://www.google.com/recaptcha/api.js?render={{ 'general.site_captcha_site_key' | site_setting }}"></script>
<script>
document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();

    grecaptcha.ready(function() {
        grecaptcha.execute('{{ "general.site_captcha_site_key" | site_setting }}', {action: 'submit'})
            .then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
                document.getElementById('contact-form').submit();
            });
    });
});
</script>
```

4. Add validation rule:

```json
{
    "validator": {
        "name": "required|string",
        "email": "required|email",
        "message": "required|string",
        "g-recaptcha-response": "recaptcha"
    }
}
```

---

## File Attachments

### Template with File Upload

```liquid
<form action="/api/send-form" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_form_key" value="{{ _form_key }}">

    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" name="name" required>
    </div>

    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" name="email" required>
    </div>

    <div class="form-group">
        <label for="resume">Resume (PDF)</label>
        <input type="file" name="resume" accept=".pdf,.doc,.docx">
    </div>

    <div class="form-group">
        <label for="portfolio">Portfolio Images</label>
        <input type="file" name="portfolio[]" multiple accept="image/*">
    </div>

    <button type="submit">Apply</button>
</form>
```

### Validation for Files

```json
{
    "validator": {
        "name": "required|string",
        "email": "required|email",
        "resume": "nullable|file|mimes:pdf,doc,docx|max:5120",
        "portfolio": "nullable|array|max:5",
        "portfolio.*": "file|image|max:2048"
    },
    "messages": {
        "resume.max": "Resume must be less than 5MB",
        "portfolio.max": "Maximum 5 portfolio images allowed",
        "portfolio.*.max": "Each image must be less than 2MB"
    }
}
```

Files are automatically attached to the notification email.

---

## Email Notifications

### Configuration

Email settings are configured in **Site Settings > Mail**:

| Setting | Description |
|---------|-------------|
| `mail.driver` | Mail driver (smtp, sendmail, etc.) |
| `mail.host` | SMTP server |
| `mail.port` | SMTP port |
| `mail.username` | SMTP username |
| `mail.password` | SMTP password |
| `mail.encryption` | Encryption (tls, ssl) |
| `mail.from_address` | Sender email |
| `mail.from_name` | Sender name |

### Email Content

The notification email includes:
- Subject: `_subject` + ` - ` + `_suffix` (if provided)
- Greeting from translations
- All form fields as `**Field**: Value`
- Attached files
- Salutation from translations

### Customizing Email Template

Create custom notification class:

```php
// app/Notifications/CustomFormNotification.php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomFormNotification extends Notification
{
    protected array $formData;
    protected $request;

    public function __construct(array $formData, $request)
    {
        $this->formData = $formData;
        $this->request = $request;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->formData['_subject'] ?? 'Form Submission')
            ->view('emails.form-submission', [
                'data' => $this->formData
            ]);

        // Attach files
        foreach ($this->request->allFiles() as $files) {
            foreach ((array) $files as $file) {
                $message->attach($file->getRealPath(), [
                    'as' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                ]);
            }
        }

        return $message;
    }
}
```

---

## Handling Form Success

### Flash Messages in Blade

```blade
@if (session('form_status') === 'success')
    <div class="alert alert-success">
        @foreach (session('form_messages') as $message)
            <p>{{ $message }}</p>
        @endforeach
    </div>
@endif

@if (session('form_status') === 'error')
    <div class="alert alert-danger">
        @foreach (session('form_messages') as $field => $messages)
            @foreach ((array) $messages as $message)
                <p>{{ $message }}</p>
            @endforeach
        @endforeach
    </div>
@endif
```

---

## Form Examples

### Newsletter Subscription

**Template:**
```liquid
<form action="/api/send-form" method="POST" class="newsletter-form">
    @csrf
    <input type="hidden" name="_form_key" value="{{ _form_key }}">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Subscribe</button>
</form>
```

**Configuration:**
```json
{
    "validator": {
        "email": "required|email"
    },
    "to_address": "newsletter@example.com"
}
```

### Callback Request

**Template:**
```liquid
<form action="/api/send-form" method="POST">
    @csrf
    <input type="hidden" name="_form_key" value="{{ _form_key }}">

    <input type="text" name="name" placeholder="Your name" required>
    <input type="tel" name="phone" placeholder="Phone number" required>

    <select name="time">
        <option value="">Preferred time</option>
        <option value="morning">Morning (9-12)</option>
        <option value="afternoon">Afternoon (12-17)</option>
        <option value="evening">Evening (17-20)</option>
    </select>

    <button type="submit">Request Callback</button>
</form>
```

**Configuration:**
```json
{
    "validator": {
        "name": "required|string|max:255",
        "phone": "required|string|max:20",
        "time": "nullable|in:morning,afternoon,evening"
    },
    "to_address": "sales@example.com"
}
```

---

## See Also

- [Blocks](blocks.md) - Form blocks documentation
- [Templating](templating.md) - Liquid template guide
- [Settings](settings.md) - Mail configuration
