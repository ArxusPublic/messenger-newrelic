<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Middleware;

use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicManager;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionNameManager;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class NewRelicMiddleware implements MiddlewareInterface
{
    /**
     * @var NewrelicManager
     */
    private $newrelicManager;

    /**
     * @var NewrelicTransactionNameManager
     */
    private $newrelicTransactionNameManager;

    public function __construct(NewrelicManager $newrelicManager, NewrelicTransactionNameManager $newrelicTransactionNameManager)
    {
        $this->newrelicManager = $newrelicManager;
        $this->newrelicTransactionNameManager = $newrelicTransactionNameManager;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!$this->newrelicManager->isEnabled()) {
            return $stack->next()->handle($envelope, $stack);
        }

        if ($envelope->all(ReceivedStamp::class)) {
            $this->newrelicManager->startTransaction();
            $this->newrelicManager->nameTransaction(
                $this->newrelicTransactionNameManager->getTransactionName($envelope)
            );
            try {
                return $stack->next()->handle($envelope, $stack);
            } catch (HandlerFailedException $e) {
                $this->newrelicManager->noticeError($e);
                throw $e;
            } finally {
                $this->newrelicManager->endTransaction();
            }
        }

        if ($envelope->all(NewrelicTransactionStamp::class)) {
            return $stack->next()->handle($envelope, $stack);
        }

        return $stack->next()->handle(
            $envelope->with(
                new NewrelicTransactionStamp(
                    $this->newrelicTransactionNameManager->getTransactionName($envelope)
                )
            ),
            $stack
        );
    }
}
