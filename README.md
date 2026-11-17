# Redirects

> Redirects is a Statamic addon that allows to easily setup simple redirect rules in the control panel.

## Features

This addon allows you to:

- redirect source URLs to custom URLs or entries
- define the redirect status code
- save notes for each redirect to remember later what it was created for

## How to Install

Run the following command from your project root:

```bash
composer require tfd/statamic-redirects
```

## How to Use

To create a redirect rule go to the new control panel section `Tools > Redirects`.  
You have to provide a source and target URL. To enable the redirect rule you have to activate the `Active` toggle.  
You also can provide notes for your future self or others, e. g. to explain what the redirect rule was made for.  

A typical redirect could look like this: 

![image](https://user-images.githubusercontent.com/2184676/138484789-f36a2ee9-fffc-4f3d-a1bd-dc3f370f71ac.png)

This configuration will redirect 
- from https://my-domain.com/halloween 
- to https://my-domain.com/calender/events-of-the-year/halloween

You can also provide absolute URLs to redirect to external sites.  
Additionally you can select an entry instead of entering a URL and the redirect addon will automatically get redirect to the URL of the entry.  

**Tip:** Do not forget to `activate` the rule via the toggle switch.

## 404 Tracking

Every URL answered with a 404 is listed in the control panel under `Tools > Redirects > Not Found`.
The list is stored in `storage/statamic/addons/statamic-redirects/not_found_pages.yaml` and limited to 1,000 entries by default; when the limit is reached, the entries with the fewest hits are removed first.

Publish the config to change this (`php artisan vendor:publish --tag=statamic-redirects-config`) or use the environment variables:

| Env | Default | |
|---|---|---|
| `STATAMIC_REDIRECTS_NOT_FOUND_ENABLED` | `true` | Disable 404 tracking completely |
| `STATAMIC_REDIRECTS_NOT_FOUND_MAX_ENTRIES` | `1000` | Maximum number of stored URLs (`0` = unlimited, not recommended) |
| `STATAMIC_REDIRECTS_NOT_FOUND_LOCK_TIMEOUT` | `0.25` | Seconds to wait for the file lock before a hit is dropped |

## Development Notes
- Tests: `composer install -d tests && tests/vendor/bin/phpunit -c tests/phpunit.xml`
  (a standalone environment without statamic/cms, see `tests/composer.json`)
- Run `npm install` from the packages root directory
- Run `npm run watch` during development
- After changing js files you have to either 
  - manually publish the cp.js file (https://statamic.dev/extending/addons#publishing-assets) or 
  - create a symlink: `ln -s /path/to/tfd/statamic-redirects/public public/vendor/statamic-redirects`
- Run `npm run production` at the end to create the distribution files
`
