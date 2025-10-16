<a href="https://s.id/standwithpalestine"><img alt="I stand with Palestine" src="https://cdn.jsdelivr.net/gh/safouene1/support-palestine-banner@master/banner-project.svg" width="100%" /></a>

# Waska NIM

[![version][packagist-version-src]][packagist-version-href]
[![total downloads][download-src]][download-href]
[![latest unstable version][unstable-version-src]][unstable-version-href]
[![codecov][codecov-src]][codecov-href]
[![license][license-src]][license-href]

Sekolah Tinggi Teknologi Wastukancana Student ID (NIM) Parser.

## Requirements

- PHP `>= 7.4` (tested on 7.4, 8.0, 8.1, 8.2, 8.3)
- Composer v2
- cURL `>= 7.19.4`

## Installation

Install the package with:

```bash
composer require wastukancana/nim
```

## Usage

```php
<?php

use Wastukancana\Nim;
use Exception;

require __DIR__ . '/vendor/autoload.php';

try {
    $nim = new Nim('211351143');
    var_dump($nim->dump());
} catch (Exception $e) {
    echo $e->getMessage();
}
```

Example dump without provider (name/gender/isGraduated null):

```php
$student = (new Nim('211351143'))->dump();
/* object(Wastukancana\Student) {
    nim: "211351143",
    name: null,
    gender: null,
    isGraduated: null,
    admissionYear: 2021,
    study: "Teknik Informatika",
    educationLevel: "S1",
    firstSemester: 1,
    sequenceNumber: 143
} */
```

### Provider (optional)

You can enrich student data by plugging one optional provider class (FQCN). Without a provider, only parser-derived fields are available (year, study, level, semester, sequence). Fields like name, gender, and graduation status will be null until a provider is used.

```php
use Wastukancana\Nim;
use Wastukancana\Provider\PDDikti;
use Wastukancana\Provider\WastuFyi;

$nim = '211351143';

// Using PDDikti provider (pass FQCN)
$student = (new Nim($nim, PDDikti::class))->dump();

// Using Wastu.FYI provider (requires a Bearer token)
$token = getenv('WASTU_FYI_TOKEN');
$student = (new Nim($nim, WastuFyi::class, ['token' => $token])))->dump();
/* object(Wastukancana\Student) {
    nim: "211351143",
    name: "SULUH SULISTIAWAN",
    gender: "M",
    isGraduated: true,
    admissionYear: 2021,
    study: "Teknik Informatika",
    educationLevel: "S1",
    firstSemester: 1,
    sequenceNumber: 143
} */
```

## Development

Run code style checks and tests locally:

```bash
composer psr2check
composer tests
```

This repository uses GitHub Actions to run the matrix CI against PHP 7.4 and 8.x with coverage reporting to Codecov.

## License

This project is licensed under [MIT License](./LICENSE).

<!-- Badges -->

[packagist-version-src]: https://poser.pugx.org/wastukancana/nim/version
[packagist-version-href]: https://packagist.org/packages/wastukancana/nim
[download-src]: https://poser.pugx.org/wastukancana/nim/downloads
[download-href]: https://packagist.org/packages/wastukancana/nim
[unstable-version-src]: https://poser.pugx.org/wastukancana/nim/v/unstable
[unstable-version-href]: https://packagist.org/packages/wastukancana/nim
[codecov-src]: https://img.shields.io/codecov/c/gh/coderwaska/waska-nim-php/main?style=flat
[codecov-href]: https://codecov.io/gh/coderwaska/waska-nim-php
[license-src]: https://poser.pugx.org/wastukancana/nim/license
[license-href]: https://packagist.org/packages/wastukancana/nim
