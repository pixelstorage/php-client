# PixelStorage PHP client

PixelStorage PHP client

## Installation

```bash
composer require pixelstorage/client
```

## Usage

```php
use PixelStorage;
use PixelStorage\Client;

// Configure
$client = new Client(
    "http://localhost",  // Usage localhost for API calls
    'public_key',
    'private_key');
    
// Use for URLs (maybe through a CDN)
$client->setHost('https://image.mysite.com/'); 

PixelStorage::configure($client);

 // Print the image with some filters
echo PixelStorage::image($image_id)->fit(203, 203, 'top-left')->url() . "\n";

```
