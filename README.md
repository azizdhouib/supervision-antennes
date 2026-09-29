# Pilotage Réseau Mobile — Antennes & Interventions

API REST + interface de supervision pour gérer un parc d'antennes de téléphonie mobile. Le projet couvre la gestion du statut des antennes, la déclaration d'interventions techniques, et leur clôture — avec une attention particulière sur la gestion de la concurrence et les performances à grande échelle (> 100k antennes).

---

## Choix techniques

### Pourquoi Laravel ?

J'ai choisi Laravel plutôt que Symfony principalement pour deux raisons concrètes sur ce projet.

La première, c'est la relation `latestOfMany()` qui m'a sauvé pas mal de temps. Le sujet demande d'embarquer la dernière intervention dans la réponse de chaque antenne — avec Eloquent ça se règle en une ligne et ça génère une sous-requête SQL optimisée, sans N+1. Avec Symfony + Doctrine j'aurais probablement eu besoin d'écrire une requête DQL custom ou de passer par un Repository avec une jointure manuelle.

La deuxième, c'est l'outillage de test. Les `FormRequest`, les `API Resources`, `RefreshDatabase` — tout ça permet d'écrire des tests d'intégration lisibles très vite, sans trop de friction.

### Gestion de la concurrence

C'est la contrainte métier la plus délicate : une seule intervention active par antenne, même sous charge.

J'ai mis en place deux niveaux de protection :

**Au niveau applicatif**, toute la logique de création d'intervention est wrappée dans une transaction avec un `SELECT ... FOR UPDATE` sur la ligne de l'antenne. Ça sérialise les requêtes concurrentes sur la même antenne — la deuxième attend que la première committe, voit l'intervention existante, et reçoit une erreur 409.

**Au niveau base de données**, un index unique partiel sur PostgreSQL (et SQLite pour les tests) garantit l'unicité au niveau physique :

```sql
CREATE UNIQUE INDEX unique_active_intervention_per_antenna
ON interventions (antenna_id)
WHERE ended_at IS NULL;
```

L'avantage de l'index partiel : il ne bloque que les interventions ouvertes (`ended_at IS NULL`). Les interventions terminées ne sont pas contraintes, donc l'historique reste illimité. C'est aussi un filet de sécurité si jamais un appel contourne la couche applicative.

---

## Stack

- **PHP 8.3** avec `declare(strict_types=1)` partout
- **Laravel 13** — Service Layer, Enums natifs PHP 8.1+, API Resources, Form Requests
- **PostgreSQL 16** en production, SQLite en dev local et pour les tests
- **Tailwind CSS** (CDN) + `fetch` natif pour le dashboard
- **PHPUnit** pour les tests d'intégration

---

## Lancer le projet

### Avec Docker

La façon la plus simple, tout est préconfiguré :

```bash
docker compose up -d --build
```

L'entrypoint s'occupe de l'installation des dépendances, de la génération de la clé et des migrations automatiquement.

Une fois les conteneurs démarrés, injecter les données de test :

```bash
docker compose exec app php artisan db:seed
```

L'application tourne sur [http://localhost:8000](http://localhost:8000).

---

### En local (sans Docker)

Si vous avez PHP 8.3 et Composer disponibles :

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000
```

Par défaut le `.env` pointe sur SQLite, donc pas besoin de PostgreSQL pour tester en local — les migrations et le seed fonctionnent directement.

---

## Tests

```bash
# Suite complète
php artisan test

# Avec le détail de chaque cas
php artisan test --testdox
```

18 tests, 165 assertions, tout en SQLite `:memory:`, < 600ms.

Ce qui est couvert :
- Listing des antennes avec pagination et filtres (`city`, `status`)
- Embarquement de la dernière intervention dans la réponse
- Authentification : rejet des requêtes non authentifiées (401)
- **Cas nominal de création** : intervention créée, antenne passe à `DOWN`
- **Cas d'erreur de doublon** : 409 Conflict si une intervention est déjà en cours
- **Clôture nominale** : `ended_at` valorisé, antenne repasse à `UP`
- Rejet si on essaie de clôturer une intervention déjà terminée (422)
- Validation stricte des champs requis

---

## Documentation API

Une interface Swagger UI est intégrée : [http://localhost:8000/docs](http://localhost:8000/docs)

Le fichier de spécification OpenAPI est aussi dispo en brut sur [http://localhost:8000/openapi.yaml](http://localhost:8000/openapi.yaml).

Les routes en écriture (`POST`, `PATCH`) nécessitent une authentification. Le token par défaut :

```
Authorization: Bearer secret-telecom-api-token-2026
```

ou via l'en-tête `X-API-KEY` si vous préférez.

---

## Exemples d'appels cURL

**Lister les antennes en panne à Paris :**
```bash
curl "http://localhost:8000/api/v1/antennas?city=Paris&status=DOWN" \
  -H "Accept: application/json"
```

**Créer une intervention :**
```bash
curl -X POST http://localhost:8000/api/v1/interventions \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer secret-telecom-api-token-2026" \
  -d '{
    "antenna_id": 1,
    "description": "Remplacement du module radio 5G défaillant",
    "technician_identity": "Thomas Dubois",
    "priority": "HIGH"
  }'
```

Réponse attendue (201) :
```json
{
  "data": {
    "id": 48,
    "antenna_id": 1,
    "description": "Remplacement du module radio 5G défaillant",
    "technician_identity": "Thomas Dubois",
    "priority": "HIGH",
    "is_active": true,
    "ended_at": null,
    "created_at": "2026-09-29T17:00:00Z",
    "updated_at": "2026-09-29T17:00:00Z"
  }
}
```

Si une intervention est déjà en cours sur cette antenne (409) :
```json
{
  "message": "L'antenne 'ANT-PAR-0001' (ID: 1) possède déjà une intervention en cours.",
  "error": "ACTIVE_INTERVENTION_CONFLICT"
}
```

**Clôturer une intervention :**
```bash
curl -X PATCH http://localhost:8000/api/v1/interventions/48/close \
  -H "Accept: application/json" \
  -H "Authorization: Bearer secret-telecom-api-token-2026"
```

Réponse (200) :
```json
{
  "data": {
    "id": 48,
    "is_active": false,
    "ended_at": "2026-09-29T17:15:32Z"
  }
}
```

---

## Dashboard

L'interface de supervision est accessible sur [http://localhost:8000](http://localhost:8000).

Elle affiche la liste des antennes avec leurs statuts, permet de filtrer par ville et par statut, et propose un bouton de clôture directement sur les antennes ayant une intervention en cours. Tout passe par `fetch` natif, pas de framework JS.

Le token API est pré-rempli dans le champ en haut à droite — vous pouvez le modifier si vous changez la valeur dans le `.env`.
