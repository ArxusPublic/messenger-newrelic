<?php

namespace Arxus\NewrelicMessengerBundle\Tests\Middleware;

use Arxus\NewrelicMessengerBundle\Newrelic\NameableInterface;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionNameManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

class NewrelicTransactionNameManagerTest extends TestCase
{
    /**
     * @var NewrelicTransactionNameManager
     */
    private $newrelicTransactionNameManager;

    public function setUp(): void
    {
        $this->newrelicTransactionNameManager = new NewrelicTransactionNameManager();
    }

    public function testTransactionNameWithoutMessageNameable(): void
    {
        $envelope = new Envelope(new \stdClass());
        $transactionName = $this->newrelicTransactionNameManager->getTransactionName($envelope);

        $this->assertEquals(\stdClass::class, $transactionName);
    }

    public function testTransactionNameWithMessageNameable(): void
    {
        $randomTransactionName = uniqid('transactionName_');
        $messageMock = $this->createMock(NameableInterface::class);
        $messageMock
            ->expects($this->once())
            ->method('getNewrelicName')
            ->willReturn($randomTransactionName);
        $envelope = new Envelope($messageMock);
        $transactionName = $this->newrelicTransactionNameManager->getTransactionName($envelope);

        $this->assertEquals($randomTransactionName, $transactionName);
    }
}
