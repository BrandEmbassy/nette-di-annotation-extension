# Nette DI annotation extension
Allows you to register class as a service via specific annotation.

## Usage
**Register extension**
```neon
extensions:
    discovery: BE\DI\Extension\AnnotationExtension('%tempDir%')    
```

**Set up extension**

`in` defines a directory in which the extension will search for possible services.
```neon
discovery:
    in: '%appDir%'
```

**Add `discovery` annotation to your service classes**
```php
<?php declare(strict_types = 1);

namespace AwesomeApp;

/**
 * @discovery
 */
class AwesomeService
{
    /* ... */
}
```
