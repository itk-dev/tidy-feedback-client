<?php

declare(strict_types=1);

namespace ItkDev\TidyFeedbackClientBundle\EventSubscriber;

use ItkDev\TidyFeedbackClient\TidyFeedbackClientHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Symfony event subscriber that injects the Tidy Feedback widget.
 */
class WidgetInjectorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TidyFeedbackClientHelper $helper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $this->helper->injectWidget($event);
    }
}
