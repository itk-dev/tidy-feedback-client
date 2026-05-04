<?php

declare(strict_types=1);

namespace ItkDev\TidyFeedbackClient;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Shared helper for embedding the Tidy Feedback widget.
 *
 * Reads configuration from environment variables and provides
 * methods for generating the widget script tag and determining
 * whether injection should occur on a given request.
 *
 * Used by both the Symfony bundle and the Drupal module.
 */
final class TidyFeedbackClientHelper
{
    private const string ENV_URL = 'TIDY_FEEDBACK_CLIENT_URL';
    private const string ENV_API_KEY = 'TIDY_FEEDBACK_CLIENT_API_KEY';
    private const string ENV_DISABLE = 'TIDY_FEEDBACK_CLIENT_DISABLE';
    private const string ENV_DISABLE_PATTERN = 'TIDY_FEEDBACK_CLIENT_DISABLE_PATTERN';

    private ?array $config = null;

    /**
     * Get the widget script tag for embedding before </body>.
     *
     * @return string the HTML script tag, or empty string if not configured
     */
    public function getWidgetSnippet(): string
    {
        $url = $this->getConfig(self::ENV_URL);
        $apiKey = $this->getConfig(self::ENV_API_KEY);

        if (empty($url) || empty($apiKey)) {
            return '';
        }

        $scriptUrl = htmlspecialchars(rtrim($url, '/').'/build/widget/widget.js', ENT_QUOTES);
        $apiKeyAttr = htmlspecialchars($apiKey, ENT_QUOTES);

        return sprintf(
            '<script src="%s" data-api-key="%s"></script>',
            $scriptUrl,
            $apiKeyAttr,
        );
    }

    /**
     * Handle a kernel.response event by injecting the widget script tag.
     *
     * Checks if the response is a successful main request, if injection
     * is enabled for the path, and if so injects the snippet before </body>.
     *
     * @param ResponseEvent $event the kernel response event
     */
    public function injectWidget(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        if (!$response->isSuccessful()) {
            return;
        }

        if (!$this->shouldInject($event->getRequest()->getPathInfo())) {
            return;
        }

        $snippet = $this->getWidgetSnippet();
        if (empty($snippet)) {
            return;
        }

        $content = $response->getContent();
        if ($content && stripos($content, '</body>') !== false) {
            $content = preg_replace('~</body>~i', $snippet.'$0', $content);
            $response->setContent($content);
        }
    }

    /**
     * Determine whether the widget should be injected for the given URI.
     *
     * Returns false if the widget is disabled globally or if the URI
     * matches the disable pattern.
     *
     * @param string $uri the request path to check
     *
     * @return bool true if the widget should be injected
     */
    public function shouldInject(string $uri): bool
    {
        if ('true' === $this->getConfig(self::ENV_DISABLE)) {
            return false;
        }

        $pattern = $this->getConfig(self::ENV_DISABLE_PATTERN);
        if ($pattern && @preg_match($pattern, $uri)) {
            return false;
        }

        return true;
    }

    /**
     * Read a configuration value from the environment.
     *
     * @param string $name the environment variable name
     *
     * @return string|null the value, or null if not set
     */
    private function getConfig(string $name): ?string
    {
        if (null === $this->config) {
            $getEnv = static fn (string $name) => getenv($name) ?: ($_ENV[$name] ?? null);

            $this->config = [
                self::ENV_URL => $getEnv(self::ENV_URL),
                self::ENV_API_KEY => $getEnv(self::ENV_API_KEY),
                self::ENV_DISABLE => $getEnv(self::ENV_DISABLE),
                self::ENV_DISABLE_PATTERN => $getEnv(self::ENV_DISABLE_PATTERN),
            ];
        }

        return $this->config[$name] ?? null;
    }
}
