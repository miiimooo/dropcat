# Deploy tool for Drupal

## Install with composer
`composer require dropcat/dropcat:dev-master`

After that you could run dropcat as: `vendor/bin/dropcat`

## Install globaly (Mac/Linux)
`wget https://dropcat.org/dropcat.phar`

`chmod +x dropcat.phar`

`sudo mv dropcat.phar /usr/local/bin/dropcat`

`dropcat --version`

## What does it do and why should anybody need it?
In the history we, Wunderkraut Sweden (former NodeOne) have used a combination 
of Jenkins and Aegir to deploy our sites. When we started develop Drupal 8 
sites, Aegir were not ready for it and we also wanted a simpler workflow 
with deploys, more fit to our normal needs. So we started to test out some tools 
out there that almost worked for us, but we realized that it should take us 
longer to adapt a tool that almost fits, then to develop our own.

### Symfony
We deciedied to develop the tool using symfony components, because Drupal uses 
some of them already, and therefor a good fit. 

### Dont't reproduce, re-use
The aim is not to replace an existing tool that do things perfect (or almost), 
the aim is to be the glue between the other tools. So in our deploy flow we use
composer (instead of drush make that we have used for all drupal 7 sites in 
Wunderkraut Sweden), dropcat and drush, with jenkins (but we could also run our
deploys localy, using whatever tools you want to run commands, like bash).

### Commands
We have now a bunch of commands to use with dropcat, and we are adding more in 
the near future.

* tar: tar:s a folder so it could be uploaded later
* upload: uplaods a tar-folder to destination, using sftp (right now)
* deploy: unpacks a tar-folder and put it in place
* symlink: creates a symlink to target folder - use case should be files-folder
as an example.
* configimport: Imports configuration (wrapper for drush config-import)

## Drupal 8
The first target for this tool is to deploy drupal 8 sites, on the list is also 
to deploy drupal 7 sites, and maybe also other types of sites after that.

## Run it
`dropcat backup --env=dev`
This uses the default settings in dropcat.yml and the overrides, if the exists, 
in dropcat.dev.yml. The config files must exits in the folder that dropcat is
runned from.

## Different commands for different tasks
To get a list of all tasks that could be used use:
`dropcat list`

To get help on a command, and explanation of commands use:
`dropcat help backup`


## Run Dropcat from jenkins
We are using dropcat from jenkins, in a excuted shell. In this example dropcat 
is installed as required in composer.json (and placed in vednor/bin by default) 
for the drupal site (also a drush alias is setup for the site:
```
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
All config for the deploy is in dropcat.stage.yml.


## Config examples
Dropcat need as a minimum a dropcat.yml in the running directory. Example is 
found in examples folder. Also examples for dev and prod environmengt is in the 
folder.


## PHPunit-testing example
./phpunit
This file points to the composer installed phpunit and runs that with
the settings from phpunit.xml. This will run all tests found in the
Tests folder. This also runs code coverage so you can see how much of
your code that has been tested.


## Disclaimer
We don't give any gurantees that this tool will work for you, you site could be
blown to pieces by it, and we don't have any support for it, but if you have 
problems using it, please create an issue.