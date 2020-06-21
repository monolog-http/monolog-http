# monolog-http

[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.com/monolog-http/monolog-http.svg?branch=master)](https://travis-ci.com/monolog-http/monolog-http)

A collection of monolog handlers that use [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP Client in order to send logs to various systems.


## Why

By leveraging the PSR-18 the developers are now able to choose the transport layer they want and customize it according to their needs (see [here](https://github.com/Seldaek/monolog/pull/1239)).

## Install

Via Composer

``` bash
$ composer require monolog-http/monolog-http
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ make phpunit
```

## Contributing

Contributions are welcome.

Please check our [issue](https://github.com/monolog-http/monolog-http/issues) if you want to help.

## Credits

- [George Mponos](https://github.com/gmponos)
- [Savvas Alexandrou](https://github.com/savvasal)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-downloads]: https://packagist.org/packages/monolog-http/monolog-http
