# DEPRECATED

Please use [gradientz/twig-express-bundle](https://github.com/gradientz/twig-express-bundle) instead.

****


# KaliopStaticHtmlBundle

This bundle offers a controller for serving simple Twig templates with "static HTML" content:

1. Renders templates from `views/static-html` directly
2. Makes index pages for `views/static-html` and subfolders

It is intended for `dev` environments only, to allow front-end designers to build a project's stylesheets, scripts, and HTML structures.


## Installation

The recommended way to install this bundle is through [Composer](http://getcomposer.org/).

Require the `kaliop/demobundle` package into your composer.json file :

```json
{
	"repositories": [
        { "type": "vcs", "url": "ssh://git@stash.kaliop.net:7999/ezp5/ezpublish5-statichtmlbundle.git" }
    ],
    "require-dev": {
        "kaliop/statichtmlbundle": "~2.0"
    }
}
```


## Getting started

1. Put your “static” templates in the `Resources/views/static-html` folder of your bundle(s).

2. Add this to your routes (for example in `routing_dev.yml`):

        static_html:
            resource: "@KaliopStaticHtmlBundle/Resources/config/routing.yml"

3. Make sure your bundle(s) with `static-html` views are listed in Assetic's configuration:

        assetic:
            bundles:
                - AcmeDefaultBundle
                - MyStaticBundle
                - AwesomeStaticBundle

## Demo pages

This bundle contains its own demo `static-html` templates. To activate the demo, add this import to your config:

```
imports:
    - { resource: "@KaliopStaticHtmlBundle/Resources/config/static-html.yml" }
```

Then navigate to `http://hostname/static/` to see a list your "static HTML" bundles.

(It's a simple config file that adds this bundle to `assetic.bundles`, and declares a Twig global variable. Feel free to imitate this pattern to create static bundle-specific config that is easy to plug in.)


## Index pages

Index pages will list:

- Subfolders of the requested folder (if any)
- Files that match this template: `[filename].[ext].twig`


## URLs patterns

You should also be able to access your `static-html` views at URLs that look like:

```
http://hostname/static/
http://hostname/static/[bundle-ref]/
http://hostname/static/[bundle-ref]/[template-name]
http://hostname/static/[bundle-ref]/[subfolder]/[template-name]
```

Where `bundle-ref` is a lowercase string that must match at least part of your bundle's name; and `template-name` is your template's file name without the `.twig` extension. Some technical details:

- The `bundle-ref` string is used to filter the list of bundles in `assetic.bundles`; if there are more than one matches, the *first* one is used.
- For HTML templates, it's okay to omit the `.html` extension in the URL. For other file types, you will need the extension (without `.twig`).

Some examples, using the example Assetic config above:

```
/static/myst/hello           ->  MyStaticBundle/.../static-html/hello.html.twig
/static/myst/hello.html      ->  MyStaticBundle/.../static-html/hello.html.twig
/static/myst/data/test.json  ->  MyStaticBundle/.../static-html/data/test.json.twig
/static/awesome/world        ->  AwesomeStaticBundle/.../static-html/world.html.twig
/static/bundle/folder/test   ->  AcmeDefaultBundle/.../static-html/folder/test.html.twig
```
