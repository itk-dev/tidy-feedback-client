<?php

declare(strict_types=1);

namespace ItkDev\TidyFeedbackClient\Drupal;

use ItkDev\TidyFeedbackClient\TidyFeedbackClientHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Drupal event subscriber that injects the Tidy Feedback widget.
 */
class TidyFeedbackClientSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TidyFeedbackClientHelper $helper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $this->helper->injectWidget($event);
    }
}
