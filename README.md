
#Deploy tool for Drupal8

Install with composer
`composer require dropcat/dropcat:dev-master`

#PHPunit-testing example
phpunit --bootstrap Tests/bootstrap.php Tests/Dropcat/Command/TarCommandTest.php
(need phpunit in your path, see https://phpunit.de/getting-started.html, and you should also have php5-xdebug installed.)
