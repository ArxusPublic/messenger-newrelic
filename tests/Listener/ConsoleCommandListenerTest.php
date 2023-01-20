<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Tests\Listener;

use Arxus\NewrelicMessengerBundle\Listener\ConsoleCommandListener;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class ConsoleCommandListenerTest extends TestCase
{
    /**
     * @var MockObject|Command
     */
    private $commandMock;

    /**
     * @var MockObject|ConsoleCommandEvent
     */
    private $eventMock;

    /**
     * @var NewrelicManager|MockObject
     */
    private $newrelicManagerMock;

    protected function setUp(): void
    {
        // Create mocks
        $this->commandMock = $this->createMock(Command::class);
        $this->eventMock = $this->createMock(ConsoleCommandEvent::class);
        $this->newrelicManagerMock = $this->createMock(NewrelicManager::class);
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
        $this->eventMock
            ->expects($this->once())
            ->method('getCommand')
            ->willReturn($this->commandMock);
        $commandListener = new ConsoleCommandListener($this->newrelicManagerMock);
        $commandListener($this->eventMock);
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
        $this->eventMock
            ->expects($this->once())
            ->method('getCommand')
            ->willReturn($this->commandMock);
        $commandListener = new ConsoleCommandListener($this->newrelicManagerMock);
        $commandListener($this->eventMock);
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
        $this->eventMock
            ->expects($this->once())
            ->method('getCommand')
            ->willReturn($this->commandMock);
        $commandListener = new ConsoleCommandListener($this->newrelicManagerMock);
        $commandListener($this->eventMock);
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
        $this->eventMock
            ->expects($this->once())
            ->method('getCommand')
            ->willReturn('');
        $commandListener = new ConsoleCommandListener($this->newrelicManagerMock);
        $commandListener($this->eventMock);
    }
}
