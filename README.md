Install:
```
composer require anboo/apple-sign-bundle:dev-master
```

Add bundle to config/bundles.php:
```php
Anboo\AppleSign\AnbooAppleSignBundle::class => ['all' => true],
```

Usage:



```php
    /** @var ASDecoder */
    private $appleDecoder;

    /**
     * @param ASDecoder $appleDecoder
     */
    public function __construct(ASDecoder $appleDecoder)
    {
        $this->appleDecoder = $appleDecoder;
    }

    public function foo()
    {
        $payload = $this->appleDecoder->decodeIdentityToken($idToken);
        dump($payload);
    }
```