eZ Publish Project Installation Procedure
==========================================

Short version (deploy dev instance)
----------------------------------


git clone ssh://git@stash.kaliop.net:7999/project/xxx.git project
    
    cd project
    
    git checkout dev/recette/prod
    
-- install bundles composer

    composer install
    
-- symlink legacy settings & extension

    cd ezpublish_legacy/settings/
    
    ln -s ../../src/ezpublish_legacy/settings/override_dev/ override
    
    ln -s ../../src/ezpublish_legacy/settings/siteaccess_dev/ siteaccess 
    
    cd ../extension
    
    ln -s ../../src/ezpublish_legacy/extension/xxxxxx
    
    cd ../
    
-- Generate Legacy autoloads 

    php bin/php/ezpgenerateautoloads.php
    
    cd ../ 
    
-- Symfony assets install

    php ezpublish/console assets:install --relative --symlink web
    
    php ezpublish/console ezpublish:legacy:assets_install --relative --symlink web

-- Symlink htaccess (if dev environment)

    cd web/ && ln -s .htacces_dev .htaccess

-- Create symlink to storage (if dev environment)

in ezpublish_legacy/var/XXX/ to /mnt/workspace/xxx/XXXX/XXXXX/ezpublish_legacy/var/foncia/storage



Full ez5 install documentation on Confluence
--------------------------------------------

https://confluence.kaliop.net/display/EZP5/Deploy+a+new+eZP5+working+instance+for+development