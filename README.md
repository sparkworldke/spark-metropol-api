<h2 align="center">Spark Metropol API</h2>

<p align="center">Laravel 10+ compatible PHP client for Metropol CRB.</p>

## About
Spark Metropol CRB (Credit Reference Bureau) API wrapper.  
A simple PHP client to communicate with the Metropol CRB API.

http://metropol.co.ke/

## Installation

```bash
composer require spark-metropol-api/spark-metropol-api
```

## Quick Example
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use SparkMetropolApi\Metropol;

$metropolPublicKey = 'your-public-key';
$metropolPrivateKey = 'your-private-key';

// set custom port and API version
$metropol = (new Metropol($metropolPublicKey, $metropolPrivateKey, <port>))
    ->withVersion('v2_1');

// verify ID number
$result = $metropol->identityVerification($id_number);

// check delinquency status
$result = $metropol->deliquencyStatus($id_number, $loan_amount);

// check credit info
$result = $metropol->creditInfo($id_number, $loan_amount);

// check consumer score
$result = $metropol->consumerScore($id_number);

// $result is a decoded JSON object (stdClass)
```

All methods return a decoded JSON object. Check the [Docs folder](/Docs) for sample results.

## Detailed Example

Use fluent setters to override options:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use SparkMetropolApi\Metropol;

$metropol = (new Metropol($publicKey, $privateKey,<port>))
    ->withVersion('v2_1')
    ->withPublicApiKey('NEW_PUBLIC_KEY')
    ->withPrivateApiKey('NEW_PRIVATE_KEY')
    ->withBaseEndpoint('https://api.metropol.co.ke')
    ->withPort(<port>);

// verify ID number
$result = $metropol->identityVerification($id_number);

// check delinquency status
$result = $metropol->deliquencyStatus($id_number, $loan_amount);

// check credit info
$result = $metropol->creditInfo($id_number, $loan_amount);

// check consumer score
$result = $metropol->consumerScore($id_number);

// $result is a decoded JSON object (stdClass)
```

## Laravel 10+ Usage

Add keys to your `.env`:
```dotenv
METROPOL_PUBLIC_KEY=your-public-key
METROPOL_PRIVATE_KEY=your-private-key
METROPOL_PORT=
METROPOL_VERSION=v2_1
```

Use in a controller or service:
```php
use SparkMetropolApi\Metropol;

class MetropolController
{
    public function check()
    {
        $metropol = (new Metropol(
            env('METROPOL_PUBLIC_KEY'),
            env('METROPOL_PRIVATE_KEY'),
            (int) env('METROPOL_PORT', 443)
        ))->withVersion(env('METROPOL_VERSION', 'v2_1'));

        $response = $metropol->identityVerification('12345678');

        return response()->json($response);
    }
}
```

Notes:
- Upgrading from `ngugijames/metropol`? Update your imports to `SparkMetropolApi\Metropol` and install `spark-metropol-api/spark-metropol-api`.
- Defaults: base endpoint `https://api.metropol.co.ke`, port `443`, version `v2`. Customize via `withBaseEndpoint`, `withPort`, and `withVersion` or pass port in the constructor.
