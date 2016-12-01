Kaliop Utils Bundle
===================

A bundle with common utilities for eZPublish 5 projects

## Installation

* Add the Stash repository url in your composer.json: 

    "repositories": [
            { "type": "vcs", "url": "ssh://git@stash.kaliop.net:7999/ezp5/ezpublish5-kalioputilsbundle.git" }
        ],


* Require the bundle via Composer:

    composer require kaliop/utilsbundle ~1.4


## Helpers

This bundle contains several helper services  that can be used for ez5 projects :

- ContentHelper (content related methods)
- LocationHelper (location related methods)
- TreeMenu Helper (load a content tree to be used for a menu, requires kaliop/solr bundle)
- TranslationHelper (get field value depending on current siteaccess language) 
- ResponseHelper (create standard & esi response with valid cache headers)
- SfCacheHelper (symfony/opcache cache clear methods)
- VarnishHelper (Varnish cache purge methods)


## XmlText Embed converter

This bundle also overrides ez5 default XmlText embed Converters in order to
 
* avoid access denied in parent page
* access embed & links 'view' parameter   


## Legacy event listener and Signal dispatcher

Warning
--------

Some slots are already attached to most signals in default ezpublish Sf kernel.
The ez default slots trigger for example content cache clear or content indexation, which means these actions will be
triggered twice if you attach to legacy events, and it can be a performance killer.
So please use this feature with caution.

This feature will allow you to listen to some specific events from legacy kernel (BO action) to trigger some action in
Sf stack.

Current available legacy events:

* content/copy
* content/delete
* content/publish
* content/create
* content/update

* location/delete
* location/hide
* location/unhide
* location/subtreecopy
* location/move

The LegacyKernelListener listens for legacy kernel post build event to attach to ezpEvents.
These events are only available from kaliop ez legacy 5.4.3  & 5.4.6  versions.
Attached events trigger the corresponding method in SignalDispatcher, in order to emit ez5 Signal.
These signals can then be atached by a custom Slot in your ez5 project.

The signals are not attached to ezpEvents by default !
You need to manually override the activation parameters in your project's ezpublish/config/parameters.yml file.
Example : 

    parameters:
         legacy_events_activation:
              content_delete: true
              content_publish: true
 

## More doc

See full [documentation on Confluence](http://confluence.kaliop.net/x/2Yeg).
