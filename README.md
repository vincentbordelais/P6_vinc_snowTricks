# OC_P6_vinc_snowTricks
## Description
* snowTRICKS est un site collaboratif dédié aux amateurs de snowboard,  permettant de partager des figures (tricks) d'apprentissage.
* Ecrit avec le framework Symfony version 6.2.6, PHP 8.1.11

* Analyse Codacy: https://app.codacy.com/gh/vincentbordelais/P6_vinc_snowTricks/issues/current?categories=Security 

* Voir les diagrammes UML dans le dossier Diagrams.

## Installation
* Choisissez un emplacement
* Clonez y le depot:  `git clone https://github.com/vincentbordelais/P6_vinc_snowTricks.git`
* `cd "votre-emplacement"/P6_vinc_snowTricks`
* Créez la base de donnée: `php bin/console doctrine:database:create`
Suivant le fichier .env, vous devriez voir le message: "Created database vinc_snowTricks for connection named default"
* Créez les tables de la base de données: `php bin/console doctrine:migrations:migrate`
* Chargez les données de test dans la base de données: `php bin/console doctrine:fixtures:load`
* Démarrez le serveur Symfony: `symfony server:start`
* Pour lire les e-mails, lancez MailHog en utilisant la commande : `mailhog`
Mailhog sera accessible à l'adresse http://0.0.0.0:8025/ et conformémant au fichier .env, il est à l'écoute des connexions SMTP entrantes sur le port 1025.
