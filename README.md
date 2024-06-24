# Symfony messenger newrelic middleware
Because symfony messenger creates a long running process, 
newrelic needs to be notified for each message that is processed.

This package provides a middleware and a command listener that handles this for you.

## Installation

Require it with composer

```bash
composer require arxus/messenger-newrelic
```

Then add the middleware to your messenger bus middlewares:

```yaml
framework:
    messenger:
        buses:
            default:
                middleware:
                    - Arxus\NewrelicMessengerBundle\Middleware\NewRelicMiddleware
```
## Usage

Originally, all message classes had to implement `NameableNewrelicTransactionInterface`:
```php
class SampleMessage implements \Arxus\NewrelicMessengerBundle\Newrelic\NameableNewrelicTransactionInterface
{
    public function getNewrelicTransactionName() {
        return 'MyCustom/Transaction-Name';
    }
}
```

Since version 0.6, it's also possible to register mappings by calling
`NewrelicTransactionNameManager::addTransactionMapping` passing the target message class FQN
and transaction name as arguments:
```php
class SampleMessage
{
    // ...
}

class SampleService
{
    public function __construct(
        private \Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionNameManager $newrelicTrxNameManager
    ) {
        $this->newrelicTrxNameManager->addTransactionMapping(SampleMessage::class, 'MyCustom/Transaction-Name');
    }
}
```

This can also be used via Symfony's Dependency Injection, with no need to create any new services or change existing service's code:
```yaml
services:
    # ...
    Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionNameManager:
      class: Arxus\NewrelicMessengerBundle\Newrelic\NewrelicTransactionNameManager
      calls:
        - addTransactionMapping: ['\App\Messenger\Message\SampleMessage', 'MyCustom/Transaction-Name']
    # ...
```

## Expected results
When newrelic is correctly installed and configured on your host,
it should report each consumed message as a separate transaction,
using the message name as the transaction name.
