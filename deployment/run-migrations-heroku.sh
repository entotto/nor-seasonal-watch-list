#!/usr/bin/env bash

# Run all necessary database migrations on the remote heroku instance

heroku run "bin/console doctrine:migrations:migrate -n; bin/console cache:clear"
