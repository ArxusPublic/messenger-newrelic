<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Newrelic;

interface NameableNewrelicTransactionInterface
{
    public function getNewrelicTransactionName();
}
