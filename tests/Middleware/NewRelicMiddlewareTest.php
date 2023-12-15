<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Tests\Middleware;

use Arxus\NewrelicMessengerBundle\Middleware\NewRelicMiddleware;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicManager;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionNameManager;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionStamp;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class NewRelicMiddlewareTest extends TestCase
{
    /**
     * @var StackInterface|MockObject
     */
    private $stackMock;

    /**
     * @var MiddlewareInterface|MockObject
     */
    private $middlewareMock;

    /**
     * @var NewrelicManager|MockObject
     */
    private $newrelicManagerMock;

    /**
     * @var NewrelicTransactionNameManager|MockObject
     */
    private $newrelicTransactionNameManagerMock;

    /**
     * @var Envelope
     */
    private $envelope;

    /**
     * @var NewRelicMiddleware
     */
    private $newrelicMiddleware;

    protected function setUp(): void
    {
        $this->stackMock = $this->createMock(StackInterface::class);
        $this->middlewareMock = $this->createMock(MiddlewareInterface::class);
        $this->stackMock->method('next')->willReturn($this->middlewareMock);
        $this->newrelicManagerMock = $this->createMock(NewrelicManager::class);
        $this->newrelicTransactionNameManagerMock = $this->createMock(NewrelicTransactionNameManager::class);
        $this->envelope = new Envelope(new \stdClass());
        $this->newrelicMiddleware = new NewRelicMiddleware($this->newrelicManagerMock, $this->newrelicTransactionNameManagerMock);
    }

    public function test_handle_send(): void
    {
        $this->newrelicTransactionNameManagerMock
            ->expects($this->once())
            ->method('getTransactionName')
            ->with($this->envelope)
            ->willReturn(\stdClass::class);
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('startTransaction');
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('nameTransaction');
        $this->newrelicManagerMock
            ->expects($this->never())
            ->method('endTransaction');
        $this->middlewareMock->method('handle')
            ->with(
                self::callback(
                    fn (Envelope $envelope) => $envelope->getMessage() === $this->envelope->getMessage()
                        && !empty($envelope->all(NewrelicTransactionStamp::class))
                        && \stdClass::class === $envelope->last(NewrelicTransactionStamp::class)
                            ->getTransactionName()
                )
            )->willReturnArgument(0);
        $this->newrelicMiddleware->handle($this->envelope, $this->stackMock);
    }

    public function test_handle_receive(): void
    {
        $stampedEnvelope = $this->envelope->with(new ReceivedStamp('mock'));
        $this->newrelicTransactionNameManagerMock
            ->expects($this->once())
            ->method('getTransactionName')
            ->with($stampedEnvelope)
            ->willReturn(\stdClass::class);
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
        $this->middlewareMock->method('handle')->willReturnArgument(0);
        $this->newrelicMiddleware->handle($stampedEnvelope, $this->stackMock);
    }

    public function test_handle_disabled(): void
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
        $this->middlewareMock->method('handle')->willReturnArgument(0);
        $this->newrelicMiddleware->handle($this->envelope, $this->stackMock);
    }

    public function test_notice_error(): void
    {
        $stampedEnvelope = $this->envelope->with(new ReceivedStamp('mock'));
        $expectedException = new HandlerFailedException($stampedEnvelope, [new \RuntimeException('expected')]);
        $this->newrelicTransactionNameManagerMock
            ->expects($this->once())
            ->method('getTransactionName')
            ->with($stampedEnvelope)
            ->willReturn(\stdClass::class);
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
            ->method('noticeError')
            ->with($expectedException);
        $this->newrelicManagerMock
            ->expects($this->once())
            ->method('endTransaction');
        $this->middlewareMock->method('handle')->willThrowException($expectedException);
        $this->expectExceptionObject($expectedException);
        $this->newrelicMiddleware->handle($stampedEnvelope, $this->stackMock);
    }
}
