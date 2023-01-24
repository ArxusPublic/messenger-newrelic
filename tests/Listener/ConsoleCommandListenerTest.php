<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Tests\Listener;

use Arxus\NewrelicMessengerBundle\Listener\ConsoleCommandListener;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleCommandListenerTest extends TestCase
{
    /**
     * @var MockObject|Command
     */
    private $commandMock;

    /**
     * @var NewrelicManager|MockObject
     */
    private $newrelicManagerMock;

    /**
     * @var ConsoleCommandEvent
     */
    private $event;

    protected function setUp(): void
    {
        // Create mocks
        $this->commandMock = $this->createMock(Command::class);
        $this->newrelicManagerMock = $this->createMock(NewrelicManager::class);

        $this->event = new ConsoleCommandEvent($this->commandMock, $this->createMock(InputInterface::class), $this->createMock(OutputInterface::class));
    }

    public function test_invoke_enabled(): void
    {
        $this->commandMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('messenger:consume');
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('endTransaction');
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $commandListener = new ConsoleCommandListener($this->newrelicManagerMock);
        $commandListener($this->event);
    }

    public function test_invoke_disabled(): void
    {
        $this->commandMock
            ->expects($this->never())
            ->method('getName')
            ->willReturn('messenger:consume');
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('endTransaction');
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
        $commandListener = new ConsoleCommandListener($this->newrelicManagerMock);
        $commandListener($this->event);
    }

    public function test_invoke_other_command(): void
    {
        $this->commandMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('other:command');
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('endTransaction');
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $commandListener = new ConsoleCommandListener($this->newrelicManagerMock);
        $commandListener($this->event);
    }

    public function test_invoke_no_command(): void
    {
        $this->commandMock
            ->expects($this->never())
            ->method('getName')
            ->willReturn('other:command');
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('endTransaction');
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('isEnabled')
            ->willReturn(true);

        $eventWithoutCommand = new ConsoleCommandEvent(null, $this->event->getInput(), $this->event->getOutput());

        $commandListener = new ConsoleCommandListener($this->newrelicManagerMock);
        $commandListener($eventWithoutCommand);
    }
}
