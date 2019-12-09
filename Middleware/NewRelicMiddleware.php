<?php

namespace Arxus\NewrelicMessengerBundle\Middleware;

use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicManager;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class NewRelicMiddleware implements MiddlewareInterface
{
    /**
     * @var NewrelicManager
     */
    private $newrelicManager;

    /**
     * NewRelicMiddleware constructor.
     */
    public function __construct(NewrelicManager $newrelicManager)
    {
        $this->newrelicManager = $newrelicManager;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!$this->newrelicManager->isEnabled()) {
            return $stack->next()->handle($envelope, $stack);
        }
        $this->newrelicManager->startTransaction();
        $this->newrelicManager->nameTransaction(get_class($envelope->getMessage()));
        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (HandlerFailedException $e) {
            $this->newrelicManager->noticeError($e);
            throw $e;
        } finally {
            $this->newrelicManager->endTransaction();
        }
    }
}
