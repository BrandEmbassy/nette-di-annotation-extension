# Nette DI annotation extension
Allows you to register class as a service via specific annotation.

## Usage
**Register extension**
```neon
extensions:
    discovery: BrandEmbassy\Nette\DI\Extensions\AnnotationExtension('%tempDir%')    
```

**Set up extension**

`in` defines a directory in which the extension will search for possible services.

`files` is optional (default `*.php`) and defines a file pattern ny which the extension searches for a possible service

```neon
discovery:
    in: '%appDir%'
    files: '*.php'
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

**Compatibility with doctrine annotation mapping**

Exclude annotation in bootstrap file of your application
```php
\Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName(\BrandEmbassy\Nette\DI\Extensions\AnnotationExtension::ANNOTATION_NAME);
```
