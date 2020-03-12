<?php

namespace Arxus\NewrelicMessengerBundle\Newrelic;

interface NamableNewrelicTransactionInterface
{
    public function getNewrelicTransactionName();
}
