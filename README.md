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

## Development Notes
- Run `npm install` from the packages root directory
- Run `npm run watch` during development
- After changing js files you have to either 
  - manually publish the cp.js file (https://statamic.dev/extending/addons#publishing-assets) or 
  - create a symlink: `ln -s /path/to/tfd/statamic-redirects/public public/vendor/statamic-redirects`
- Run `npm run production` at the end to create the distribution files
`
