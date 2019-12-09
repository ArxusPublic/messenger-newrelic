<?php

namespace Arxus\NewrelicMessengerBundle\Newrelic;

class NewrelicManager
{
    /**
     * Is the newrelic extension loaded
     */
    public function isEnabled(): bool
    {
        return extension_loaded('newrelic');
    }

    /**
     * Immidiately end the newrelic transaction
     */
    public function endTransaction(): void
    {
        newrelic_end_transaction();
    }

    /**
     * Start a new newrelic transaction
     */
    public function startTransaction(): void
    {
        newrelic_start_transaction(ini_get('newrelic.appname'));
    }

    /**
     * Give the current transaction a custom name
     */
    public function nameTransaction(string $name): void
    {
        newrelic_name_transaction($name);
    }

    /**
     * Report an exception to newrelic
     */
    public function noticeError($exception): void
    {
        newrelic_notice_error($exception);
    }
}
