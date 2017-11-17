<?php

/*
 * This file is part of the Payments Hub project.
 *
 * Copyright 2017 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

require 'recipe/symfony3.php';

// Configuration
set('ssh_type', 'native');
set('ssh_multiplexing', true);
set('writable_use_sudo', true);
set('keep_releases', 5);

set('shared_dirs', array_merge(get('shared_dirs'), [
    'var/jwt',
]));

set('env_vars', 'SYMFONY_ENV={{env}} DATABASE_USER={{app.database_user}} DATABASE_NAME={{app.database_name}} DATABASE_PORT={{app.database_port}} DATABASE_PASSWORD={{app.database_password}} SYMFONY_SECRET={{app.secret}}');

set('repository', 'https://github.com/payhelper/payments-hub.git');
set('default_stage', 'local');
set('composer_options', function () {
    $options = '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader';

    return 'prod' !== get('env') ? $options : sprintf('%s --no-dev', $options);
});

set('clear_paths', function () {
    return 'prod' !== get('env') ? [] : ['web/app_*.php', 'web/config.php'];
});

// Servers
inventory('servers.yml');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');

task('database:migrate', function () {
    run('{{env_vars}} {{bin/php}} {{bin/console}} doctrine:migrations:migrate {{console_options}}');
})->desc('Migrate database');
