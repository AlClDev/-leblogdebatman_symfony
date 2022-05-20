# Projet Le blog de Batman

## Installation

```
git clone https://github.com/AlClDev/-leblogdebatman_symfony.git
```

### Modifier les paramètres d'environnements dans le fichier .env pour le faire correspondre à votre environnement (Accès base de données, clés Google, recaptcha, etc...)
```

#Accès base de données à modifier
DATABASE_URL="mysql://root:@127.0.0.1:3306/leblogdebatman?serverVersion=5.7&charset=utf8mb4"

#clés Google recaptcha à modifier
GOOGLE_RECAPTCHA_SITE_KEY=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
GOOGLE_RECAPTCHA_PRIVATE_KEY=XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

### Déplacer le terminal dans le dossier cloné du projet
```
cd leblogdebatman
```

### Taper les commandes suivantes :
```
composer install
symfony console doctrine:database:create
symfony console make:migration
symfony console doctrine:migration:migrate
symfony console doctrine:fixtures:load
symfony console assets:install public
```

Les fixtures créeront :
* Un compte admin(email: admin@a.a, mot de psse : Alban21! )
* 10 comptes utilisateurs (email aléatoire, mot de passe : Alban21!)
* 200  articles
* Entre 0 et 10 commentaires par article

### Démarrer le serveur Symfony :
```
symfony serve
```