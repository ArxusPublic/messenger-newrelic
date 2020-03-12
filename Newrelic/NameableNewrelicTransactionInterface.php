<?php

namespace Arxus\NewrelicMessengerBundle\Newrelic;

interface NameableNewrelicTransactionInterface
{
    public function getNewrelicTransactionName();
}
