# Ngrok Changes

Cette note décrit les changements appliqués au backend Symfony pour permettre un test simple depuis un téléphone via ngrok.

## Pourquoi ce changement

En configuration initiale, le projet exposait principalement le backend en HTTPS local.

Pour un test mobile avec ngrok, cette approche compliquait les choses :

- certificat local non reconnu sur telephone ;
- configuration Caddy orientee `localhost` ;
- besoin d'une URL publique simple pour Flutter.

L'objectif a donc ete de rendre le backend accessible en HTTP local sur le port 80, puis de laisser ngrok fournir le HTTPS public.

## Fichiers modifies

- `compose.yaml`
- `frankenphp/Caddyfile`
- `docs/jwt-authentication.md`

## Changement 1 : exposition locale du backend en HTTP

Dans `compose.yaml`, le service `php` a ete ajuste pour :

- ecouter sur le port 80 ;
- ne plus publier les ports 443 et HTTP/3 ;
- utiliser `DEFAULT_URI=http://localhost` ;
- utiliser `SERVER_NAME=:80` pour accepter les requetes HTTP sur tous les hosts utiles, y compris le host ngrok.

Effet attendu :

- backend local disponible sur `http://localhost` ;
- ngrok peut forwarder `http://localhost:80` sans conflit TLS local.

## Changement 2 : desactivation du HTTPS automatique de Caddy

Dans `frankenphp/Caddyfile`, les points suivants ont ete appliques :

- `auto_https off`
- site servi en `http://{$SERVER_NAME:localhost}`
- suppression du domaine ngrok code en dur dans le fichier

Effet attendu :

- le backend local reste simple ;
- le HTTPS public est delegue a ngrok ;
- aucun domaine ngrok ne doit etre commite dans le depot.

## Changement 3 : compatibilite avec les domaines ngrok

Le backend ne doit pas etre limite a `localhost` seulement.

Le passage a `SERVER_NAME=:80` permet a Caddy de servir correctement :

- `http://localhost`
- `http://127.0.0.1`
- le domaine public fourni par ngrok

Sans ce changement, on pouvait obtenir une page blanche ou une reponse vide via ngrok.

## Procedure ngrok

### 1. Installer ngrok

Sous Windows, ngrok peut deja etre installe via le package MSIX ou un binaire local.

Verifier la presence de ngrok :

```powershell
ngrok version
```

### 2. Configurer l'authtoken localement

Ne jamais enregistrer l'authtoken dans le depot Git.

Commande :

```powershell
ngrok config add-authtoken VOTRE_TOKEN
```

### 3. Lancer le tunnel

```powershell
ngrok http 80
```

Exemple de resultat :

```text
Forwarding  https://xxxxxx.ngrok-free.app -> http://localhost:80
```

## URL utiles

En local :

- `http://localhost/api`
- `http://localhost/api/docs`
- `http://localhost/api/login_check`

Via ngrok :

- `https://xxxxxx.ngrok-free.app/api`
- `https://xxxxxx.ngrok-free.app/api/docs`
- `https://xxxxxx.ngrok-free.app/api/login_check`

## Point d'attention sur l'ecran ngrok gratuit

Sur une offre ngrok gratuite, une page d'avertissement peut s'afficher la premiere fois dans un navigateur.

Deux solutions :

- cliquer sur `Visit Site` dans le navigateur ;
- pour un client HTTP ou Flutter, envoyer le header `ngrok-skip-browser-warning: true`.

## Verification minimale

Test local de l'entrypoint :

```powershell
curl.exe -i http://localhost/api
```

Test local du login :

```powershell
$body = @{ email = 'test@gmail.com'; password = 'test1234' } | ConvertTo-Json -Compress
Invoke-RestMethod -Uri 'http://localhost/api/login_check' -Method Post -ContentType 'application/json' -Body $body
```

Test ngrok de la documentation :

```powershell
curl.exe -k -H "ngrok-skip-browser-warning: true" -i https://xxxxxx.ngrok-free.app/api/docs
```

## Resume

Le principe retenu est simple :

- HTTP en local ;
- HTTPS public via ngrok ;
- aucune URL ngrok ou aucun secret ngrok dans le depot.

Cette configuration est plus adaptee aux tests Flutter sur telephone que le HTTPS local classique.