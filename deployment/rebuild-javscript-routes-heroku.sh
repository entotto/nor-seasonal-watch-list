#!/usr/bin/env bash

# Rebuild the cache of expose Symfony routes for JavaScript use.

heroku run "bin/console fos:js-routing:dump --target=public/js/fos_js_routes.json"
