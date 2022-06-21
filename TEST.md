## Sujet

Développer une petite application web qui permet de gérer des articles à partir d’une API. Il faut s’imaginer que c’est le début d’une grosse application qui sera amenée à évoluer. Chaque élément doit être pensé pour pouvoir évoluer, être maintenu facilement et être performant.

- L’API doit nécessiter une authentification pour fonctionner
- L’API doit être RESTful et supporter le format JSON au minimum
- Un article doit être composé au minimum des éléments suivants :
    - Titre (max 128 caractères)
    - Contenu (texte)
    - Auteur (user)
    - date de publication (date et heure)
    - Status = brouillon, publié, supprimé
        - Si on passe en status “publié” on ne peut pas mettre de date de publication car elle se renseigne automatiquement à aujourd’hui
        - Si on passe en status “brouillon” on peut mettre une date (future) de publication facultativement
- L’api doit contenir un système de validation
- Il est nécessaire d’avoir un moyen de lister les articles publiés.
- Il est nécessaire d’avoir des tests unitaires.
- Vous devrez inclure un fichier `README.md` avec la documentation de l’API et qui détaille aussi les dépendances et les étapes nécessaires pour faire fonctionner le projet.

Imaginez que ce README sera utilisé par les développeurs de votre équipe mais aussi pour le déploiement sur les environnements de staging et de production de l’application.

La partie front n’est pas requise. L’évaluation portera uniquement sur le côté back/api.

## Quelques notes et contraintes supplémentaires

- Vous êtes libre d’utiliser le framework de votre choix, mais il est nécessaire d’utiliser un framework open-source avec une documentation disponible librement sur internet.
- Toute fonctionnalité supplémentaire sera appréciée (gestion des droits, recherche, pagination, données de test, script d’installation, format html/markdown dans les articles, …).
- Il n’y a aucune contrainte sur les modules, base de données, librairies, outils, *etc*.

## Évaluation

- L’évaluation portera principalement sur deux points :
    - La qualité du code et le fait qu’il n’y ai pas de redondance ou de code inutile (le code doit être lisible facilement sans commentaire)
    - Le choix des technos et outils qui devra être justifié à l’issue du test
    - Le fait qu’il n’y ai pas (ou peu) de logique métier dans les contrôleurs, mais que celle-ci soit déportée dans des services spécialisés par exemple
- Les éléments secondaires qui pèseront de manière non significative dans l’évaluation :
    - Le type d’authentification de l’API
    - La structure de la base de donnée, les relations et les éventuels indexes utilisés
