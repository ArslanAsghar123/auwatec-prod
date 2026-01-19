# SwpaBackup
Create backup of database & media

Our recommended installation method for advanced users is installation via composer and Shopware CLI:

composer require swpa/backup
php bin/console plugin:install SwpaBackup
php bin/console plugin:activate SwpaBackup
php bin/console cache:clear


If you want to create backup immediately, you can use the Shopware CLI command:  bin/console swpa:backup:run force

Without the parameter "force" the command will just start the backup scheduler

To enable or disable maintenance mode you can use following commands: 
bin/console swpa:maintenance:mode:disable 
or 
bin/console swpa:maintenance:mode:disable

The command bin/console swpa:backup:clear - will be check and clean old backups.
