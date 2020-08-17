# Deploy tool for Drupal
  
Forked from https://gitlab.wklive.net/wk-public/dropcat

[Please read our blog-series about dropcat](https://wunderkraut.se/dropcat)

## Install with composer
`composer require miiimooo/dropcat:dev-master`

After that you may run dropcat as: `vendor/bin/dropcat`

## Install globally (Mac/Linux)

For this forked version of dropcat
| `curl -L0 https://github.com/miiimooo/dropcat/releases/download/2.0.0/dropcat.phar -o /usr/local/bin/dropcat`
| `chmod +x /usr/local/bin/dropcat`
| `dropcat about`

## What does it do and why would anybody need it?
Historically we at Wunderkraut Sweden (formerly NodeOne) have used a combination 
of Jenkins and Aegir to deploy our sites. When we started development of  Drupal 8 
sites, Aegir was not ready, and we also wanted a simpler deploy workflow,
more fitting to our everyday needs. So we started to test out some tools 
out there that almost worked for us, but we realized that it should take us 
longer to adapt a tool that almost fits, than to develop our own.

## Symfony
We decided to develop the tool using symfony components, because Drupal uses 
some of them already, and it is therefore a good fit. 

## Dont't reproduce, re-use
The aim is not to replace an existing tool that do things perfect (or almost), 
the aim is to be the glue between the other tools. So in our deploy flow we use
composer (instead of drush make that we have used for all drupal 7 sites in 
Wunderkraut Sweden), dropcat and drush, with jenkins (but we could also run our
deploys locally, using whatever tools you wish to run commands, like your own 
terminal).

### Wrapping drush, why?
Some of the commands are just wrappers around drush, like `dropcat backup` and 
`dropcat site-install`. We have dropcat as an wrapper because we could use 
variables from yaml-files in a consistent way. Some parts could be changed to be 
wrappers for drupal console instead in the future, or changed to use our own 
defined functions instead - the idea is to keep dropcat consistent, but 
changing what it is built on the way as needed.

## Commands
We have now a bunch of commands to use with dropcat, and we are adding more in 
the near future.

* backup: backups db to path.
* prepare: creates drush-alias for site and db on host.
* tar: archives a folder for later upload
* upload: uploads a tar-folder to destination
* move: unpacks a tar-folder and put it in place
* symlink: creates a symlink to target folder - use-case could be for example files-folder
* site-install: install a site 
* configimport: imports configuration (wrapper for drush config-import)
* init: uses our template to create a drupal 8 site with a profile
* about: what is a terminal app without an about?
* jenkins-build: Build a jenkins job

## First Drupal 8, then 7
The first target for this tool is to deploy drupal 8 sites, on the list is also 
to deploy drupal 7 sites, and maybe also other types of sites after that. "It is 
all just a bunch of files in different languages", as a famous bulgarian web developer 
said once!

## Run it
`dropcat backup`
This uses the default settings in dropcat.yml. If the system variable DROPCAT_ENV 
is set to dev, dropcat uses dropcat.dev.yml, if that exists. 
The config yaml-file must exits in the folder that dropcat is
run from.

## Different commands for different tasks
To get a list of all tasks that may be used use:
`dropcat list`

To get help on a command, and explanation of commands use:
`dropcat help backup`


## Run Dropcat from jenkins
We are using dropcat from jenkins, in a executed shell. In this example dropcat 
is installed as required in composer.json (and placed in vednor/bin by default) 
for the drupal site (also a drush alias is setup for the site:
```
export DROPCAT_ENV=stage
export ENV=stage
export BUILD_DATE="$(date +"%Y%m%d")"

# got to application dir, that is our web folder
cd application

composer install

# only need to be runned once (creates drush alias on deploy server and database 
# on dbhost
dropcat prepare

dropcat tar --folder=${WORKSPACE}/application --temp-path=${WORKSPACE}/ -v
dropcat upload --tar_dir=${WORKSPACE}/
dropcat symlink
dropcat site-install
dropcat update

```
All config for the deploy is in dropcat.stage.yml in application folder.

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
We don't give any gurantee that this tool will work for you, your site could be
nuked from orbit by it, and we don't have any support for it, but if you have 
problems using it, please create an issue.
