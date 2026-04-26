# JWT Authentication

Cette note résume l'implémentation actuelle de l'authentification JWT du projet Symfony/API Platform.

## Objectif

Permettre a un client Flutter de :

- envoyer `email` et `password` au backend ;
- recevoir un token JWT si les identifiants sont valides ;
- reutiliser ce token sur les routes protegees via le header `Authorization: Bearer <token>`.

## Composants utilises

- Symfony Security
- API Platform
- LexikJWTAuthenticationBundle
- PostgreSQL

## Fichiers importants

- `config/packages/security.yaml`
- `config/packages/lexik_jwt_authentication.yaml`
- `config/routes.yaml`
- `.env`
- `src/Entity/User.php`
- `src/Command/CreateTestUserCommand.php`

## Configuration mise en place

### 1. Bundle JWT

Le bundle Lexik JWT est installe via Composer.

Il utilise :

- une cle privee pour signer les tokens ;
- une cle publique pour les verifier ;
- une passphrase definie dans `.env`.

Les variables utilisees sont :

- `JWT_SECRET_KEY`
- `JWT_PUBLIC_KEY`
- `JWT_PASSPHRASE`

### 2. Firewall de login

La route de connexion est :

- `POST /api/login_check`

Le firewall `login` accepte une requete JSON de cette forme :

```json
{
  "email": "test@gmail.com",
  "password": "test1234"
}
```

Si les identifiants sont valides, Symfony retourne :

```json
{
  "token": "<jwt>"
}
```

### 3. Firewall API

Toutes les routes `^/api` restent protegees par JWT, sauf les exceptions explicitement laissees publiques pour le confort de test local :

- `/api/login_check`
- `/api`
- `/api/docs`
- quelques endpoints techniques API Platform (`/api/contexts`, `/api/errors`, `/api/validation_errors`, `/.well-known`)

Cela permet :

- de tester le login facilement ;
- de consulter la doc locale ;
- de garder les vraies ressources metier protegees.

## Utilisateur de test

Une commande Symfony cree ou met a jour un utilisateur de test idempotent :

```bash
php bin/console app:create-test-user
```

Identifiants actuels :

- email : `test@gmail.com`
- mot de passe : `test1234`
- nom : `test`
- prenom : `test`

Le mot de passe est stocke hashe par Symfony.

## Tests locaux

### Recuperer un token

Exemple PowerShell :

```powershell
$body = @{ email = 'test@gmail.com'; password = 'test1234' } | ConvertTo-Json -Compress
[System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
Invoke-RestMethod -Uri 'https://localhost/api/login_check' -Method Post -ContentType 'application/json' -Body $body
```

### Utiliser un token sur une route protegee

```powershell
$body = @{ email = 'test@gmail.com'; password = 'test1234' } | ConvertTo-Json -Compress
[System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
$login = Invoke-RestMethod -Uri 'https://localhost/api/login_check' -Method Post -ContentType 'application/json' -Body $body
$headers = @{ Authorization = "Bearer $($login.token)" }
Invoke-RestMethod -Uri 'https://localhost/api/users' -Method Get -Headers $headers
```

## Comportement attendu

- `GET /api/login_check` : erreur normale, car seule la methode `POST` est autorisee
- `POST /api/login_check` avec les bons identifiants : retourne un token
- `GET /api/users` sans token : `401 JWT Token not found`
- `GET /api/users` avec token : acces autorise

## Point d'attention pour Flutter

- sur mobile, `localhost` ne pointe pas vers le PC de developpement ;
- pour un vrai telephone, il faut utiliser soit l'IP locale du PC, soit ngrok ;
- si HTTPS local pose un probleme de certificat, ngrok est souvent plus simple pour les tests mobiles.

## Test mobile avec ngrok

La stack locale est configuree pour exposer le backend en HTTP sur le port 80 :

- `http://localhost/api`
- `http://localhost/api/login_check`

Cela permet d'utiliser ngrok simplement avec un tunnel HTTP vers le port local 80.

### Pre-requis

- ngrok installe localement ;
- authtoken configure localement sur la machine de developpement ;
- ne jamais stocker cet authtoken dans le depot.

### Commandes utiles

Configurer l'authtoken localement :

```powershell
ngrok config add-authtoken VOTRE_TOKEN
```

Lancer un tunnel vers le backend local :

```powershell
ngrok http 80
```

### URL a utiliser dans Flutter

Une fois le tunnel lance, ngrok fournit une URL publique du type :

- `https://xxxxxx.ngrok-free.app`

Dans Flutter, la base URL devient alors :

- `https://xxxxxx.ngrok-free.app`

Et le login se fait sur :

- `https://xxxxxx.ngrok-free.app/api/login_check`
