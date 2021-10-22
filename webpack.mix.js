const mix = require('laravel-mix');

mix.js('resources/js/cp.js', 'public/js/cp.js').vue({ version: 2 });
