Opérations à effectuer à chaque déploiement des sources
=======================================================

Noter dans ce fichier toutes les commandes à exécuter lors d'un déploiement des sources.
Ex : 

Vidage caches
- php ezpublish/console ca:c

Dump des assets
- php ezpublish/console assetic:dump --no-debug

Dump Routes FOSJS
- php ezpublish/console fos:js-routing:dump

* Si MAJ xmlinstaller :
 cd ezpublish_legacy && php extension/ezxmlinstaller/bin/php/xmlinstaller.php -s site_admin --template=xmlinstaller/process