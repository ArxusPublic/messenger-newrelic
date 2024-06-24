<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Newrelic;

use Symfony\Component\Messenger\Stamp\StampInterface;

class NewrelicTransactionStamp implements StampInterface
{
    public function __construct(
        private string $transactionName
    ) {
    }

    public function getTransactionName(): string
    {
        return $this->transactionName;
    }
}
