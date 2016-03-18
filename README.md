# Deploy tool for Drupal8

Install with composer
`composer require dropcat/dropcat:dev-master`

## Run it
`/path/to/run/dropcat backup --env=dev`
This uses the default settings in dropcat.yml and the overrides, if the exists, in dev_dropcat.yml

## Examples
Dropcat need as a minimum a dropcat.yml in the running directory. Example is found in examples folder. Also examples for dev and prod environmengt is in the folder.



## PHPunit-testing example
./phpunit
This file points to the composer installed phpunit and runs that with
the settings from phpunit.xml. This will run all tests found in the
Tests folder. This also runs code coverage so you can see how much of
your code that has been tested.
