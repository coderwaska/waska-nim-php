<a href="https://s.id/standwithpalestine"><img alt="I stand with Palestine" src="https://cdn.jsdelivr.net/gh/safouene1/support-palestine-banner@master/banner-project.svg" width="100%" /></a>

# Waska NIM

[![version][packagist-version-src]][packagist-version-href]
[![total downloads][download-src]][download-href]
[![latest unstable version][unstable-version-src]][unstable-version-href]
[![codecov][codecov-src]][codecov-href]
[![license][license-src]][license-href]

Sekolah Tinggi Teknologi Wastukancana Student ID (NIM) Parser.

## Requirements

- PHP `>= 7.4`
- Composer v2
- cURL `>= 7.19.4`

## Installation

Install the package with:

```bash
composer require wastukancana/nim
```

## Example

```php
<?php

use Wastukancana\Nim;

require __DIR__ . '/vendor/autoload.php';

try {
    $nim = new Nim('211351143');

    var_dump($nim->dump());
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

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
