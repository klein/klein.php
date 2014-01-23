# Unit Testing

Unit tests are a crucial part of developing a routing engine such as Klein.
Added features or bug-fixes can have adverse effects that are hard to find
without a lot of testing, hence the importance of unit testing.

This project uses [PHPUnit](https://github.com/sebastianbergmann/phpunit/) as
its unit testing framework.

The tests all live in `/tests` and each test extends an abstract class
`AbstractKleinTest`

To test the project, simply run `php composer.phar install --dev` to download
a common version of PHPUnit with composer and run the tests from the main
directory with `./vendor/bin/phpunit`