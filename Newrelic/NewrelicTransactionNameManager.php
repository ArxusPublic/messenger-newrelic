<?php

namespace Arxus\NewrelicMessengerBundle\Newrelic;

use Symfony\Component\Messenger\Envelope;

class NewrelicTransactionNameManager
{
    public function getTransactionName(Envelope $envelope): string
    {
        $message = $envelope->getMessage();
        if ($message instanceof NamableNewrelicTransactionInterface) {
            return $message->getNewrelicTransactionName();
        }

        return get_class($message);
    }
}
