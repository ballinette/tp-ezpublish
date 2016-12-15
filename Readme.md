# IUT Montpellier - TD eZ Publish

Sources : https://github.com/ballinette/tp-ezpublish
Liens utiles : https://github.com/ballinette/tp-ezpublish/blob/master/links.md

## Commandes utiles :

* Pour démarrer une VM Vagrant :
`vagrant up`

* Pour stopper une VM Vagrant :
`vagrant halt`

* Pour se connecter à une VM Vagrant en cours d'exécution :
`vagrant ssh`

* Pour exécuter une commande sur une VM Vagrant :
`vagrant ssh -c '<COMMAND>'`

* Pour démarrer un environnement Docker (depuis la VM Vagrant) :
`docker-compose up`
`docker-compose up -f <CONF_FILE>`

* Pour lister les containers Docker en cours d'exécution :
`docker ps`

* Pour exécuter une commande sur un container Docker :
`docker exec -ti <CONTAINER_NAME> <COMMAND>`

* Pour vider le cache eZ Publish / Symfony (depuis le container Docker) :
`php ezpublish/console cache:clear --no-warmup`

### Aliases utiles à créer pour la suite :

* Démarrer Vagrant et l'environnement docker en une commande :
`alias docker-start="vagrant up && vagrant ssh -c 'cd /vagrant && docker-compose up'"`

* Accéder à la console eZ Publish :
`alias ezcli="vagrant ssh -c 'docker exec -ti vagrant_ezpublish_1 su - www-data'"`

## Préparation des environnements de développement

* Dans /home/vb/ : décompresser l'archive `ezpublish2.tar.gz`

* Créer des liens symboliques des dossiers créés vers votre home directory :
```
ln -s /home/vb/.vagrant.d ~/
ln -s /home/vb/VirtualBox\ VMs ~/
```

* Ajouter la box Vagrant :
```
vagrant box add williamyeh/debian-jessie64-docker /chemin/vers/image/virtualbox.box
```

* Se déplacer dans /home/vb/tp-ezpublish/ et démarrer l'environnemnt Vagrant/Docker :
```
cd /home/vb/tp-ezpublish/
docker-start
```

* tester les Accès aux services :
  * MySQL : mysql -h 172.30.1.20 -u root -padmin
    * Vérifier la présence de la base ezpublish. Si elle n'existe pas ou si elle est vide, importer les données : `mysql -h 172.30.1.20 -u root -padmin < mysql/ezpublish.sql`
  * Varnish : http://172.30.1.20/
  * Apache : http://172.30.1.20:81/

## Finalisation de l'installation eZ Publish

se connecter sur la console eZ Publish :
`ezcli`

puis exécuter les commandes suivantes :

* Mise à jour des dépendances :
`composer install`

* Installation des assets issues des extensions "legacy" :
`php ezpublish/console ezpublish:legacybundles:install_extensions --relative`
`php ezpublish/console ezpublish:legacy:assets_install --symlink --relative web`
`pushd ezpublish_legacy && php bin/php/ezpgenerateautoloads.php -e && popd`

* Installation des assets issues des bundles Symfony :
`php ezpublish/console assets:install --symlink --relative web`
`php ezpublish/console assetic:dump --env=dev web`

* Accéder à eZ Publish :
** Front office : http://172.30.1.20/
** Back office : http://172.30.1.20/ezadmin (admin / admin)

## Exercice 1 : Controller Symfony simple

Objectif : créer un Bundle Symfony avec un controller répondant à une route `/hello/{name}`
Résultat attendu : une page simple avec affichage du nom passé en paramètre.
* Créer et activer le Bundle `IutTrainingBundle`
* Créer le controller `DefaultController`
* Créer le template d'affichage
* Configurer la route

## Exercice 2 : Créer un siteaccess personnalisé pour l'affichage du blog

* Modifier la racine du site 'www' pour pointer sur le noeud "Blog"
* Surcharger le controller par défaut de la classe "Blog" :
  * utilisation d'un template de layout personnalisé au lieu de celui utilisé par défaut
  * afficher uniquement le titre de chaque "blog_post" enfant.

Documentations utiles :
- https://doc.ez.no/display/EZP/View+provider+configuration
- https://doc.ez.no/display/EZP/2.+Browsing%2C+finding%2C+viewing
- http://apidoc.ez.no/sami/trunk/NS/html/eZ/Publish/API/Repository.html
- https://doc.ez.no/display/EZP/Criteria+reference

## Exercice 3 : Ajouter les vues pour les blog posts

* Configurer de manière globale le layout par défaut
* Créer un controller et une vue personnalisée pour les blog posts :
  * titre
  * nom de l'auteur
  * contenu

## Execice 4 : Ajouter des feuilles de style CSS avec Assetics

Documentation utile :
http://symfony.com/doc/2.8/assetic/asset_management.html

