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
## Expected results
When newrelic is correctly installed and configured on your host,
it should report each consumed message as a separate transaction,
using the message name as the transaction name.