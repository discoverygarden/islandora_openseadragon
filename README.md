# Islandora OpenSeadragon

# Introduction

An Islandora viewer module using OpenSeadragon. Works with large image
datastreams (JPEG-2000). Supports a custom Djatoka tilesource and aIIIF
tilesource.

Based in spirit from the JS component of Kevin Clarke's
[FreeLib-Djatoka](https://github.com/ksclarke/freelib-djatoka)

## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/discoverygarden/islandora)
* [Tuque](https://github.com/islandora/tuque)
* [OpenSeadragon](https://github.com/openseadragon/openseadragon/)
* [Drupal Token Module](https://www.drupal.org/project/token)

## Installation

Install as
[usual](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules).

[Download](https://github.com/openseadragon/openseadragon/releases/download/v2.3.1/openseadragon-bin-2.3.1.zip)
and install the Openseadragon library to your sites/libraries folder, or run
`drush openseadragon-plugin`. Openseadragon 2.3.1 is known to work well with Islandora.

Note: If you use the Drush command, it is advisable to Move (not copy) the
install script to your `.drush` folder and run it.

## Configuration

### Djatoka Image Server

#### Drupal

Set the paths for 'Djatoka server base URL' and configure OpenSeadragon in
Configuration » Islandora » OpenSeadragon (admin/islandora/module).

![Configuration](https://camo.githubusercontent.com/c1bf991b5cc758a4420444564a91b286007e6f6e/687474703a2f2f692e696d6775722e636f6d2f4e6566597169432e706e67)

If you have an *existing* install it's required to update Openseadragon to it's
latest version. You can do this quickly with the provided Drush command.

```bash
drush openseadragon-plugin
```

#### Apache Reverse Proxy

Reverse proxy config: We make the assumption that we (reverse) proxy Djatoka,
to fix the same-origin issue.

For Apache, with Drupal running on the same box as Apache, a couple lines like:

```
ProxyPass /adore-djatoka http://localhost:8080/adore-djatoka
ProxyPassReverse /adore-djatoka http://localhost:8080/adore-djatoka
```

in the Apache config somewhere (either the main apache.conf, httpd.conf, or in
and arbitrarily named `*.conf` in your Apache's conf.d directory should suffice
to establish the reverse proxy.

In Debian derived systems one will need to create location entries for each
proxy or remove the Deny from All in mod_proxy's conf file.

### IIIF

Any [IIIF](http://iiif.io) image server can be used the the IIIF tile source.
The IIIF tile source provides a full URL to the datastream to be displayed as
the IIIF `identifier`. The IIIF server needs to be configured to resolve this
full URL to retrieve the image. 

The [Cantaloupe 🍈](https://medusa-project.github.io/cantaloupe/) IIIF image
server can be configured to resolve these identifiers using the 
[`HttpResolver`](https://medusa-project.github.io/cantaloupe/manual/3.3/resolvers.html#HttpResolver)
with no prefix specified.

## Documentation

Further documentation for this module is available at [our
wiki](https://wiki.duraspace.org/display/ISLANDORA/Open+Seadragon)

## Troubleshooting/Issues

Having problems or solved one? Create an issue, check out the Islandora Google
groups.

* [Users](https://groups.google.com/forum/?hl=en&fromgroups#!forum/islandora)
* [Devs](https://groups.google.com/forum/?hl=en&fromgroups#!forum/islandora-dev)

or contact [discoverygarden](http://support.discoverygarden.ca).

## Maintainers/Sponsors

Current maintainers:

* [discoverygarden](http://www.discoverygarden.ca)

## Development

If you would like to contribute to this module, please check out the helpful
[Documentation](https://github.com/Islandora/islandora/wiki#wiki-documentation-for-developers),
[Developers](http://islandora.ca/developers) section on Islandora.ca and create
an issue, pull request and or contact
[discoverygarden](http://support.discoverygarden.ca).

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
