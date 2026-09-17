# Osclass

Osclass is an open-source, self-hosted PHP platform for building classifieds websites and online marketplaces.

[![Latest release](https://img.shields.io/github/v/release/osclass-classifieds/Osclass)](https://github.com/osclass-classifieds/Osclass/releases)
[![License](https://img.shields.io/github/license/osclass-classifieds/Osclass)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-777BB4)](https://osclass-classifieds.com/installation)
[![Last commit](https://img.shields.io/github/last-commit/osclass-classifieds/Osclass)](https://github.com/osclass-classifieds/Osclass/commits/main)
[![Documentation](https://img.shields.io/badge/docs-osclass--classifieds-0d9ecc)](https://docs.osclass-classifieds.com)
[![Website](https://img.shields.io/badge/website-osclass--classifieds.com-056786)](https://osclass-classifieds.com)

Build sites for:

- General classifieds
- Local marketplaces
- Real estate
- Jobs
- Vehicles
- Services
- Niche marketplaces

The core application provides listings, categories, locations, custom fields, user accounts, search, moderation, media management, administration and an extensible theme/plugin system.

[Website](https://osclass-classifieds.com) | [Download](https://osclass-classifieds.com/download) | [Documentation](https://docs.osclass-classifieds.com) | [Developer Guide](https://docs.osclass-classifieds.com/developer-guide) | [Changelog](https://osclass-classifieds.com/changelog) | [Live Demo](https://osclass-classifieds.com/demo) | [Community](https://forums.osclasspoint.com)

## Project status

Osclass is actively maintained and developed.

This repository contains the official Osclass source code and release history maintained by the OsclassPoint team.

Current stable release: **8.3.1** (PHP 7.2 or higher; PHP 8.x recommended, including PHP 8.5).

For current releases, installation instructions, documentation and project news:

- Website: https://osclass-classifieds.com
- Documentation: https://docs.osclass-classifieds.com
- Downloads: https://osclass-classifieds.com/download
- Changelog: https://osclass-classifieds.com/changelog
- Developer Guide: https://docs.osclass-classifieds.com/developer-guide

## Why Osclass?

Osclass is designed specifically for classifieds and marketplace applications rather than adapting a general-purpose CMS.

It provides:

- PHP/MySQL application stack
- Listings and categories
- Location hierarchy
- Category-specific custom fields
- User accounts and moderation
- Search and filtering
- SEO-friendly URLs
- Themes and plugins
- Hooks and filters
- Multilingual support
- Administration interface
- Payment and marketplace extensions
- Self-hosted deployment

The core application can be installed on conventional PHP hosting without requiring a Node.js or Composer-based build process for a basic installation.

## Build your own marketplace

Osclass can be used as the software foundation for:

- Local classifieds
- Job boards
- Real estate marketplaces
- Vehicle marketplaces
- Service directories
- Niche marketplaces
- Community selling platforms

The application is self-hosted, so the operator controls the domain, hosting, database, source code and marketplace configuration.

[Get started](https://osclass-classifieds.com/get-started)

## For developers

Osclass is designed to be extended through themes, plugins, hooks and filters.

Developers can build:

- Custom listing types
- Marketplace functionality
- Payment integrations
- Search and filtering extensions
- Custom fields
- Importers and integrations
- Notification systems
- Custom themes
- Administration extensions
- API integrations
- Automation and scheduled jobs

Prefer extension points over modifying core files whenever possible.

Developer documentation: https://docs.osclass-classifieds.com/developer-guide

## Architecture

```text
oc-admin/       Administration interface
oc-content/     Themes, plugins, uploads and site-specific files
oc-includes/    Core application, DAOs, helpers and libraries
```

`oc-content` is kept separate from the application core. Themes, plugins, uploads, and other site-specific files can remain in place when the core is updated.

The administration directory can be changed from its default location using `OC_ADMIN_FOLDER` in `config.php`.

Intended customization boundary:

```text
Theme/plugin customization
        |
Hooks and filters
        |
Osclass APIs
        |
Core application
```

### Core functionality

**Custom search URLs.** Permalink patterns such as `{sCity}/{sCategory}` can produce URLs such as `/bremen/for-sale`. Rules can use strict or fuzzy parameter matching and can be prioritized when multiple rules apply.

**Subdomains.** Installations can use country, region, or language subdomains. Depending on configuration, subdomains can be combined with geo-based redirects and country restrictions.

**Languages.** Osclass includes more than 60 community-maintained language packs. The backoffice includes tools for working with PO/MO translation files.

**Structured data.** Core generates Schema.org, Open Graph, and Twitter Card metadata. This does not depend entirely on the active theme.

**Caching.** Supported backends include Memcache, Memcached, APC/APCu, and Redis.

**Custom fields.** Fields can be attached to categories and listings. Supported types include phone, email, color, number, and URL fields with matching HTML input types.

## Extension development

### Plugins

Location: `oc-content/plugins/`

A plugin can contain PHP, JavaScript, CSS, templates, configuration, database installation or update logic, and administration pages.

Plugins should use Osclass APIs, hooks, filters, and DAO/database interfaces instead of changing core files.

### Themes

Location: `oc-content/themes/`

Themes control frontend presentation. A theme can contain PHP templates, CSS, JavaScript, images and other assets, configuration, and theme-specific PHP. Themes can use Osclass data and the hooks and filters API without modifying the application core.

### Hooks and filters

Osclass exposes hooks and filters across listings, users, search, mail, administration, and structured data.

Use a hook to run code at a defined point:

```php
osc_add_hook('header', 'my_function_in_head');
```

Use a filter to modify a value. `item_post_data` runs before a listing insert and receives the insert array:

```php
osc_add_filter('item_post_data', 'my_function_before_listing_saved');
```

`pre_send_mail_filter` can stop an outgoing message by returning:

```php
array('stop' => true)
```

`user_locale_changed` runs when the active locale changes.

Check for an existing hook or filter before modifying core code. It usually makes an extension easier to maintain across Osclass updates.

See the [Developer Guide](https://docs.osclass-classifieds.com/developer-guide) for the extension API.

## Development

Osclass is written in PHP and uses MySQL or MariaDB for persistent storage. There is no Composer or Node build step for a basic local install.

### Requirements

- PHP 7.2 or higher; PHP 8.x is recommended (PHP 8.5 is supported in 8.3.1)
- MySQL or MariaDB
- PHP extensions: MySQLi, GD, cURL
- ImageMagick is optional and can be used for image processing
- Apache or Nginx with URL rewriting configured
- Git

Exact requirements can depend on PHP version, server configuration, theme, and installed plugins.

### Clone

```bash
git clone https://github.com/osclass-classifieds/Osclass.git
cd Osclass
```

Create a development database, point your local web server at the project directory, and run the Osclass installer in the browser.

This repository does not currently include Git submodules or a bundled Docker/dev stack.

### Database and DAO layer

Osclass provides database access through its DAO layer. Core data includes listings, users, categories, locations, comments, custom fields, and related application data.

Extensions that need persistent data should generally manage their own database structures rather than changing Osclass core tables unnecessarily.

### Core development

Before changing core behavior, check whether an existing hook, filter, or plugin API can provide the required extension point.

Core work may involve controllers, DAO queries, listing lifecycle, search, authentication, categories and locations, URL routing, mail, caching, administration, internationalization, security, and structured data.

## Production installation

For production sites, use the latest stable release package rather than deploying an arbitrary development branch.

Download: https://osclass-classifieds.com/download

GitHub Releases: https://github.com/osclass-classifieds/Osclass/releases

Developers working on integrations or extensions can clone this repository and use the source directly in a development environment.

Osclass includes a web installer. It checks the server environment, connects to your database, and creates the initial administrator account.

1. Download the latest release.
2. Extract Osclass on your web server.
3. Create a MySQL or MariaDB database.
4. Make the required directories writable.
5. Open the website in your browser.
6. Follow the installation wizard.
7. Log in to the administration panel.

[Installation guide](https://osclass-classifieds.com/installation)

## Releases

Stable Osclass releases are published on the official website and as [GitHub Releases](https://github.com/osclass-classifieds/Osclass/releases).

Before upgrading a production installation, review:

- release notes
- compatibility requirements
- PHP version requirements
- theme/plugin compatibility

Download: https://osclass-classifieds.com/download

## Updating

Back up the database and files before upgrading.

Because site-specific files live in `oc-content`, a normal core update can replace the application files without replacing themes, plugins, uploads, and other site content.

1. Back up the database.
2. Back up the installation.
3. Check the release notes.
4. Check theme and plugin compatibility.
5. Replace the core files.
6. Complete any required upgrade steps.

Do not remove `oc-content` during a normal core update.

See the [upgrade documentation](https://docs.osclass-classifieds.com/upgrade-osclass-i104) for the current procedure.

## How to contribute

The Osclass core repository is maintained by the OsclassPoint development team.

Developers are welcome to:

- inspect the source code
- test releases
- report reproducible problems
- suggest improvements
- build plugins and themes
- build integrations
- share implementation feedback

Core code changes are reviewed and merged through the project's internal development workflow. Pull requests submitted directly to this repository are automatically closed and are not used as the project's core contribution channel.

For documentation, development questions and support, use:

https://docs.osclass-classifieds.com/

## Issues and support

GitHub Issues are not used as the support channel for this repository.

- Documentation: https://docs.osclass-classifieds.com/
- Community discussion and support: https://forums.osclasspoint.com/
- Current releases: https://osclass-classifieds.com/download
- Product extensions: https://osclasspoint.com/

## Project activity

Osclass is actively developed as part of the OsclassPoint ecosystem.

The project receives ongoing:

- Core development
- PHP compatibility updates
- Security and maintenance fixes
- Performance improvements
- Documentation updates
- Release updates
- Theme and plugin compatibility work

See [CHANGELOG.txt](CHANGELOG.txt) and https://osclass-classifieds.com/changelog for the release history.

## History

Osclass launched in 2010. In 2019, the original company behind it shut down. The 3.9.0 changelog records this as the first release after that shutdown.

The project continued under new stewardship, with version 4.0.0 released in September 2020. The later jump from 4.5 to 8.0.0 was intentional, avoiding version collisions and confusion with other Osclass forks.

Since then, development has continued through regular releases, PHP compatibility updates, security fixes, and changes to the application, including the cookie and session layer, utf8mb4 support, custom search URL rules, and database query improvements.

## License

Osclass is licensed under the Apache License 2.0. See [LICENSE](LICENSE).

Osclass moved to the Apache License 2.0 in version 3.3.2 in 2014, and releases since then have been distributed under that license.

## Links

- Website: https://osclass-classifieds.com
- Download: https://osclass-classifieds.com/download
- Documentation: https://docs.osclass-classifieds.com
- Developer Guide: https://docs.osclass-classifieds.com/developer-guide
- Installation: https://osclass-classifieds.com/installation
- Changelog: https://osclass-classifieds.com/changelog
- Live Demo: https://osclass-classifieds.com/demo
- Community: https://forums.osclasspoint.com
- Themes and plugins: https://osclasspoint.com
- GitHub Releases: https://github.com/osclass-classifieds/Osclass/releases
