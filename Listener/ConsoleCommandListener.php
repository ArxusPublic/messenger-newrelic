<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Listener;

use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class ConsoleCommandListener
{
    /**
     * @var NewrelicManager
     */
    private $newrelicManager;

    /**
     * ConsoleCommandListener constructor.
     */
    public function __construct(NewrelicManager $newrelicManager)
    {
        $this->newrelicManager = $newrelicManager;
    }

    public function __invoke(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if (!$command instanceof Command) {
            return;
        }
        if (!$this->newrelicManager->isEnabled()) {
            return;
        }
        if ('messenger:consume' !== $command->getName()) {
            return;
        }
        $this->newrelicManager->endTransaction();
    }
}
