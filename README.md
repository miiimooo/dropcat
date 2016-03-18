# Deploy tool for Drupal

Install with composer
`composer require dropcat/dropcat:dev-master`

## Drupal 8
The first target for this tool is to deploy drupal 8 sites, on the list is also 
to deploy drupal 7 sites, and maybe also other types of sites after that.

## Run it
`/path/to/run/dropcat backup --env=dev`
This uses the default settings in dropcat.yml and the overrides, if the exists, 
in dev_dropcat.yml. The config files must exits in the folder that dropcat is
runned from.

## Different commands for different tasks
To get a list of all tasks that could be used use:
`/path/to/run/dropcat list`

To get help on a command, and explanation of commands use:
`/path/to/run/dropcat help backup`


## Run Dropcat from jenkins
We are using dropcat from jenkins, in a excuted shell. In this example dropcat is installed as required in composer.json for the drupal site:
```
# Setting drush path @todo: fix this in server
export BACKUPNAME="${JOB_NAME}_${BUILD_NUMBER}"
export ALIAS="mysite_latest_stage"
export ENV='stage'
export SITEALIAS="mysite"

composer install

vendor/bin/dropcat backup
vendor/bin/dropcat tar
vendor/bin/dropcat upload
vendor/bin/dropcat deploy

drush @${SITEALIAS} si myprofile --account-name="admin" --account-pass="xxx" -y
drush @${SITEALIAS} cim staging -y
drush @${SITEALIAS} entup -y
drush @${SITEALIAS} updb -y
drush @${SITEALIAS} uli -y

```
All config for the deploy is in dropcat.yml


## Config examples
Dropcat need as a minimum a dropcat.yml in the running directory. Example is 
found in examples folder. Also examples for dev and prod environmengt is in the folder.


## PHPunit-testing example
./phpunit
This file points to the composer installed phpunit and runs that with
the settings from phpunit.xml. This will run all tests found in the
Tests folder. This also runs code coverage so you can see how much of
your code that has been tested.
