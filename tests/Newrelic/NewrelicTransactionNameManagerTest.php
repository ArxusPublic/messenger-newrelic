<?php declare(strict_types=1);

namespace Arxus\NewrelicMessengerBundle\Tests\Middleware;

use Arxus\NewrelicMessengerBundle\Newrelic\NameableNewrelicTransactionInterface;
use Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionNameManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;

class NewrelicTransactionNameManagerTest extends TestCase
{
    /**
     * @var NewrelicTransactionNameManager
     */
    private $newrelicTransactionNameManager;

    protected function setUp(): void
    {
        $this->newrelicTransactionNameManager = new NewrelicTransactionNameManager();
    }

    public function test_transaction_name_without_message_nameable(): void
    {
        $envelope = new Envelope(new \stdClass());
        $transactionName = $this->newrelicTransactionNameManager->getTransactionName($envelope);

        $this->assertEquals(\stdClass::class, $transactionName);
    }

    public function test_transaction_name_with_message_nameable(): void
    {
        $randomTransactionName = uniqid('transactionName_');
        $messageMock = $this->createMock(NameableNewrelicTransactionInterface::class);
        $messageMock
            ->expects($this->once())
            ->method('getNewrelicTransactionName')
            ->willReturn($randomTransactionName);
        $envelope = new Envelope($messageMock);
        $transactionName = $this->newrelicTransactionNameManager->getTransactionName($envelope);

        $this->assertEquals($randomTransactionName, $transactionName);
    }
}
