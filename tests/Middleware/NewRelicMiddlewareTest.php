<?php

namespace Arxus\NewrelicMessengerBundle\Tests\Middleware;

use Arxus\NewrelicMessengerBundle\Middleware\NewRelicMiddleware;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class NewRelicMiddlewareTest extends TestCase
{
    /**
     * @var MockObject|StackInterface
     */
    private $stackMock;

    /**
     * @var MockObject|MiddlewareInterface
     */
    private $middlewareMock;

    /**
     * @var NewrelicManager|MockObject
     */
    private $newrelicManagerMock;

    /**
     * @var Envelope
     */
    private $envelope;

    /**
     * @var NewRelicMiddleware
     */
    private $newrelicmiddleware;

    public function setUp(): void
    {
        $this->stackMock = $this->createMock(StackInterface::class);
        $this->middlewareMock = $this->createMock(MiddlewareInterface::class);
        $this->stackMock->method('next')->willReturn($this->middlewareMock);
        $this->newrelicManagerMock = $this->createMock(NewrelicManager::class);
        $this->envelope = new Envelope(new \stdClass());
        $this->newrelicmiddleware = new NewRelicMiddleware($this->newrelicManagerMock);
    }

    public function testHandle(): void
    {
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('startTransaction');
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('nameTransaction')
            ->with(\stdClass::class);
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('endTransaction');
        $this->newrelicmiddleware->handle($this->envelope, $this->stackMock);
    }

    public function testHandleDisabled(): void
    {
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('startTransaction');
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('nameTransaction');
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('endTransaction');
        $this->newrelicmiddleware->handle($this->envelope, $this->stackMock);
    }
}
