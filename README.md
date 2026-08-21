# Osclass

Osclass is an open-source, self-hosted PHP platform for building classifieds and marketplace websites.

It provides listings, categories, locations, custom fields, search, user accounts, comments, moderation, themes, plugins, and administration tools out of the box.

You can use the same core application for a general classifieds site or a more specific marketplace such as real estate, vehicles, jobs, services, or local classifieds.

## A quick note on the project

Osclass launched in 2010. In 2019, the original company behind it shut down. The 3.9.0 changelog records this as the first release after the official Osclass shutdown.

The project continued under new stewardship, with version 4.0.0 released in September 2020. The later jump from 4.5 to 8.0.0 was intentional, avoiding version collisions and confusion with other Osclass forks.

Since then, development has continued through regular releases, PHP compatibility updates, security fixes, and changes to the underlying application. This includes the cookie and session layer, utf8mb4 support, custom search URL rules, and database query improvements.

See the [full changelog](https://osclass-classifieds.com/changelog) for the complete release history.

## Requirements

* PHP 7.2 or higher; PHP 8.x is recommended
* MySQL or MariaDB
* PHP extensions:

  * MySQLi
  * GD
  * cURL
* ImageMagick is optional and can be used for image processing
* Apache or Nginx with URL rewriting configured

The exact requirements can depend on the PHP version, server configuration, theme, and installed plugins.

## Installation

Osclass includes a web installer. It checks the server environment, connects to your database, and creates the initial administrator account.

1. Download the latest release.
2. Extract Osclass on your web server.
3. Create a MySQL or MariaDB database.
4. Make the required directories writable.
5. Open the website in your browser.
6. Follow the installation wizard.
7. Log in to the administration panel.

[Installation guide](https://osclass-classifieds.com/installation)

## How it's structured

```text
oc-admin/       Administration interface and backoffice functionality

oc-content/     Site-specific files:
                themes
                plugins
                uploads
                language files
                cache

oc-includes/    Core application:
                PHP classes
                DAOs
                helpers
                bundled libraries
```

`oc-content` is kept separate from the application core. Themes, plugins, uploads, and other site-specific files can therefore remain in place when the core is updated.

The administration directory can also be changed from its default location using `OC_ADMIN_FOLDER` in `config.php`.

## Themes and plugins

Most customization should happen through themes and plugins rather than by editing core files.

Themes live in:

```text
oc-content/themes/
```

Plugins live in:

```text
oc-content/plugins/
```

A plugin can contain PHP code, templates, JavaScript, CSS, configuration, administration pages, and its own database installation or upgrade routines.

Themes control the frontend and can include their own templates, styles, and JavaScript.

Both can use Osclass hooks and filters to interact with the application.

## Hooks and filters

Osclass exposes hundreds of hooks and filters across the application, including listings, users, search, email, administration, and structured data.

Use a hook to execute code at a defined point:

```php
osc_add_hook('item_post_data', 'my_function_after_listing_saved');
```

Use a filter to modify a value:

```php
osc_add_filter('item_post_data', 'my_function_before_listing_saved');
```

Examples of extension points include:

* `pre_send_mail_filter`
* `structured_data_title_filter`
* `user_locale_changed`

For example, `pre_send_mail_filter` can be used to stop an outgoing email by returning:

```php
['stop' => true]
```

Check for an existing hook or filter before modifying core code. It usually makes an extension easier to maintain across Osclass updates.

See the [Osclass documentation](https://docs.osclass-classifieds.com/developer-guide) for the extension API and developer reference.

## Development

Osclass is written in PHP and uses MySQL/MariaDB for persistent storage. The codebase includes the application core, administration interface, DAO layer, themes, plugins, and bundled libraries.

### Local development

A typical development environment consists of:

* PHP
* MySQL or MariaDB
* Apache or Nginx
* Git

Clone the repository with its submodules:

```bash
git clone --recursive https://github.com/osclass-classifieds/Osclass.git
cd Osclass
```

If the repository was already cloned without submodules:

```bash
git submodule update --init --recursive
```

Create a development database, configure your local web server, and run the normal Osclass installer.

### Plugin development

Plugins should use Osclass APIs, hooks, filters, and DAO/database interfaces instead of changing core files.

Typical plugin work includes:

* adding listing functionality
* integrating external APIs
* changing search behavior
* adding administration tools
* modifying email and notifications
* adding payment integrations
* adding custom fields
* extending user functionality
* changing structured data
* adding scheduled operations

Plugin documentation is available in the [Osclass documentation](https://docs.osclass-classifieds.com/).

### Theme development

Themes are stored under:

```text
oc-content/themes/
```

A theme can contain:

* PHP templates
* CSS
* JavaScript
* images and other assets
* configuration
* theme-specific PHP functionality

Themes can use Osclass data and the hooks and filters API without modifying the application core.

### Database and DAO layer

Osclass uses MySQL/MariaDB and provides database access through its DAO (Data Access Object) layer.

Core data includes listings, users, categories, locations, comments, custom fields, and related application data.

Extensions that need persistent data should generally manage their own database structures rather than changing Osclass core tables unnecessarily.

### Core development

Core development may involve:

* controllers and application actions
* DAO queries
* listing lifecycle
* search and filtering
* authentication and users
* categories and locations
* URL routing
* email and notifications
* caching
* administration
* internationalization
* security and input validation
* structured data

Before changing core behavior, check whether an existing hook, filter, or plugin API can provide the required extension point.

### Documentation

Developer documentation covers installation, configuration, plugins, themes, hooks, filters, and other parts of the platform:

[docs.osclass-classifieds.com](https://docs.osclass-classifieds.com/)

## Core functionality

### Custom search URLs

The search URL system allows custom permalink patterns such as:

```text
{sCity}/{sCategory}
```

which can produce URLs such as:

```text
/bremen/for-sale
```

Rules can use strict or fuzzy parameter matching and can be prioritized when multiple rules apply.

### Subdomains

Osclass supports subdomain-based installations for countries, regions, or languages. Depending on configuration, subdomains can be combined with geo-based redirects and country restrictions.

### Languages

Osclass includes more than 60 community-maintained language packs. The backoffice also includes tools for working with PO/MO translation files.

### Structured data

Core generates Schema.org, Open Graph, and Twitter Card metadata. This does not depend entirely on the active theme.

### Caching

Osclass supports multiple caching backends, including:

* Memcache
* Memcached
* APC/APCu
* Redis

### Custom fields

Custom fields can be attached to categories and listings. Supported field types include phone, email, color, number, and URL fields with appropriate HTML input types.

## Updating

Back up the database and files before upgrading.

Because site-specific files live in `oc-content`, a normal core update can replace the application files without replacing themes, plugins, uploads, and other site content.

Before upgrading:

1. Back up the database.
2. Back up the installation.
3. Check the release notes.
4. Check theme and plugin compatibility.
5. Replace the core files.
6. Complete any required upgrade steps.

Do not remove `oc-content` during a normal core update.

See the [documentation](https://docs.osclass-classifieds.com/upgrade-osclass-i104)) for the current upgrade procedure.

## Project status

This repository is maintained as a **read-only source mirror**.

It provides a public, version-controlled copy of the Osclass source code and release history. External pull requests and GitHub issue reports are not accepted in this repository.

For current releases, downloads, documentation, support, and project information, use:

[osclass-classifieds.com](https://osclass-classifieds.com)

## License

Osclass is licensed under the Apache License 2.0. See [LICENSE](LICENSE).

Osclass moved to the Apache License 2.0 in version 3.3.2 in 2014, and releases since then have been distributed under that license.

## Links

* **Website & downloads:** https://osclass-classifieds.com
* **Documentation:** https://docs.osclass-classifieds.com
* **Installation:** https://osclass-classifieds.com/installation
* **Changelog:** https://osclass-classifieds.com/changelog
* **Community & support:** https://forums.osclasspoint.com
* **Themes & plugins:** https://osclasspoint.com
