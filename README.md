# GenjGoogleDriveBundle

Features:

* provides command to sync files from a Google Drive folder with a local folder
* provides action to view the synced files as a slideshow



## Requirements

* Symfony 2.5
* GooglApiClient - https://github.com/google/google-api-php-client



## Installation

Add this to your composer.json:

```
    ...
    "require": {
        ...
        "genj/google-drive-bundle": "dev-master"
        ...
```

Then run `composer update`. After that is done, enable the bundle in your AppKernel.php:

```
# app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles() {
        $bundles = array(
            ...
            new Genj\GoogleDriveBundle\GenjGoogleDriveBundle()
            ...
```

Copy the routing rules from ```Resources/config/routing.yml``` to your routing.yml.


## Configuration

You need to get the following information from the Google API Console ( https://code.google.com/apis/console ):

* Service account API key file (this file is expected to be in the ```app/config/``` folder)
* Service account e-mail address

Add these to your parameters.yml:

```
genj_google_drive.service_account_key_file:
genj_google_drive.service_account_email:
```


## Usage

Run the sync:

```
$ php app/console genj:google-drive:sync
```

