# Déploiement cible sur brullon-32

Cette note fige la cible de déploiement actuelle du projet web et de l'application mobile.

## Cible retenue

- VM : `brullon-32`
- IP privée VPN : `10.187.22.32`
- Domaine public : `https://std32.beaupeyrat.com`
- Objectif : remplacer complètement ngrok pour les échanges mobile ↔ API

## Pré-requis d'accès

L'accès SSH à la VM passe par le VPN Stormshield du rectorat.

Pré-requis opératoires :

- se connecter au VPN Stormshield depuis Internet ;
- utiliser son compte nominatif Beaupeyrat ;
- vérifier que le VLAN serveur `10.187.22.0/25` est joignable ;
- se connecter ensuite en SSH sur `brullon@10.187.22.32`.

Ne pas stocker les mots de passe VPN, SSH ou sudo dans le dépôt.

## Vérifications initiales sur la VM

Une fois connecté en SSH :

```bash
hostname
ip a
git --version
docker --version
docker compose version
sudo apt update
sudo apt upgrade -y
```

Résultat attendu :

- la VM répond bien comme `brullon-32` ;
- Docker et Docker Compose sont disponibles ;
- le système est à jour avant le premier déploiement.

## DNS et HTTPS

Le domaine public retenu est `std32.beaupeyrat.com`.

Sur cette infrastructure, le cas le plus probable est le meme que sur `std31.beaupeyrat.com` :

- le HTTPS public est termine par un reverse proxy externe ;
- la VM recoit du HTTP interne avec les headers `X-Forwarded-*` ;
- Caddy/FrankenPHP ne doit pas essayer d'emettre lui-meme le certificat public.

Points à vérifier avant lancement de la stack :

- le DNS public du domaine pointe bien vers l'IP publique exposée par l'infrastructure ;
- le proxy ou pare-feu amont redirige bien les requetes vers la VM en HTTP ;
- les headers `X-Forwarded-For`, `X-Forwarded-Host`, `X-Forwarded-Proto`, `X-Forwarded-Port` sont conserves ;
- aucun mecanisme de redirection locale ne reboucle en HTTPS sur la VM.

Dans ce mode, le certificat TLS public est gere en amont. La VM reste en HTTP applicatif.

## Récupération du projet

Sur la VM :

```bash
git clone https://github.com/Maxence192003/Web_gestion_stock.git
cd Web_gestion_stock
```

Le dépôt mobile peut aussi être cloné sur la VM pour archivage ou consultation, mais il n'est pas nécessaire au runtime serveur.

## Variables de production à préparer

Ne pas commiter ces valeurs. Les définir uniquement sur la VM via l'environnement shell, un fichier `.env.prod.local` non versionné, ou un mécanisme de secrets adapté.

Variables minimales :

- `SERVER_NAME=:80`
- `DEFAULT_URI=https://std32.beaupeyrat.com`
- `APP_SECRET=<secret long et aléatoire>`
- `CADDY_MERCURE_JWT_SECRET=<secret long et aléatoire>`
- `SYMFONY_TRUSTED_PROXIES=REMOTE_ADDR`
- variables PostgreSQL si les valeurs par défaut doivent être remplacées

Le projet utilise actuellement PostgreSQL, pas MySQL.

## Déploiement Docker Compose

Depuis la racine du projet web :

```bash
docker compose -f compose.yaml -f compose.prod.yaml build --pull --no-cache
SERVER_NAME=:80 \
DEFAULT_URI=https://std32.beaupeyrat.com \
SYMFONY_TRUSTED_PROXIES=REMOTE_ADDR \
APP_SECRET=changer_cette_valeur \
CADDY_MERCURE_JWT_SECRET=changer_cette_valeur \
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

Ensuite, vérifier l'état :

```bash
docker compose -f compose.yaml -f compose.prod.yaml ps
docker compose -f compose.yaml -f compose.prod.yaml logs --tail=100 php
```

## Vérifications applicatives

Depuis la VM :

```bash
curl -I http://localhost
curl -H 'X-Forwarded-Proto: https' -H 'X-Forwarded-Host: std32.beaupeyrat.com' http://localhost/api
curl -H 'X-Forwarded-Proto: https' -H 'X-Forwarded-Host: std32.beaupeyrat.com' http://localhost/api/docs
```

Test de login :

```bash
curl -X POST http://localhost/api/login_check \
  -H 'X-Forwarded-Proto: https' \
  -H 'X-Forwarded-Host: std32.beaupeyrat.com' \
  -H 'Content-Type: application/json' \
  -d '{"email":"test@gmail.com","password":"test1234"}'
```

Résultat attendu :

- le certificat HTTPS est valide ;
- `/api` et `/api/docs` répondent via le proxy amont en HTTPS ;
- `/api/login_check` renvoie un token JWT avec des identifiants valides ;
- Symfony genere bien des URLs en `https://std32.beaupeyrat.com` derriere le proxy.

## Impact côté mobile

L'application Flutter utilise désormais `https://std32.beaupeyrat.com` comme URL par défaut.

Pour un test ponctuel avec une autre cible, par exemple ngrok ou une préproduction, lancer Flutter avec :

```bash
flutter run --dart-define=API_BASE_URL=https://autre-cible.example
```

## Sortie de ngrok

Ngrok reste utile seulement comme solution de secours pendant un développement local.

La cible finale à conserver est :

- web/API : `https://std32.beaupeyrat.com`
- mobile : `https://std32.beaupeyrat.com`

Quand le domaine répond correctement en HTTPS via le proxy amont et que le login mobile fonctionne, ngrok n'est plus nécessaire.