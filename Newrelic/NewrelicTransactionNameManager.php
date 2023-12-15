<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Newrelic;

use Symfony\Component\Messenger\Envelope;

class NewrelicTransactionNameManager
{
    /**
     * @var array<class-string, string>
     */
    private array $transactionNameRegistry = [];

    /**
     * @param class-string $class
     * @param string       $transactionName
     *
     * @return $this
     */
    public function addTransactionMapping(string $class, string $transactionName): self
    {
        $this->transactionNameRegistry[$class] = $transactionName;

        return $this;
    }

    public function getTransactionName(Envelope $envelope): string
    {
        $stamp = $envelope->last(NewrelicTransactionStamp::class);
        if (null !== $stamp) {
            return $stamp->getTransactionName();
        }

        $message = $envelope->getMessage();
        if ($message instanceof NameableNewrelicTransactionInterface) {
            return $message->getNewrelicTransactionName();
        }

        return $this->transactionNameRegistry[$class = get_class($message)] ?? $class;
    }
}
