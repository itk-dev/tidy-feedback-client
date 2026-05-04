# Tidy Feedback Client

A lightweight PHP package for embedding the
[Tidy Feedback Collector](https://github.com/itk-dev/tidy-feedback-collector)
widget on Symfony and Drupal sites.

The client automatically injects the widget script tag before `</body>` on all
HTML responses. No database, no routes — just a script tag and an API key.

## Installation

```bash
composer require itk-dev/tidy-feedback-client
```

### Symfony

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    ItkDev\TidyFeedbackClientBundle\TidyFeedbackClientBundle::class => ['all' => true],
];
```

### Drupal

Enable the module:

```bash
drush en tidy_feedback_client
```

## Configuration

Set these environment variables in your `.env.local` (Symfony) or hosting
environment (Drupal):

```dotenv
# Required — the URL of your Tidy Feedback Collector instance
TIDY_FEEDBACK_CLIENT_URL=https://your-collector-domain.com

# Required — the API key for your website (from the collector admin)
TIDY_FEEDBACK_CLIENT_API_KEY=your-api-key-here

# Optional — set to "true" to disable widget injection entirely
TIDY_FEEDBACK_CLIENT_DISABLE=false

# Optional — regex pattern for paths where the widget should not appear
TIDY_FEEDBACK_CLIENT_DISABLE_PATTERN=
```

## How it works

The client listens to the `kernel.response` event and injects the following
script tag before `</body>` on successful HTML responses:

```html
<script src="https://your-collector-domain.com/build/widget/widget.js"
        data-api-key="your-api-key-here"></script>
```

The widget handles everything else — UI, screenshots, and posting feedback to
the collector API.
