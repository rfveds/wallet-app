# WalletApp

The application that I wrote for a course assignment at university using Test Driven Development approach.

## Features that will be implemented

### Wallet
- [x] CRUD for Wallet
- [x] creating and managing wallets (cards, cash, etc.)
- [x] recording inflows and outflows (account balance cannot drop below zero)
### Operation
- [x] CRUD for operations
- [x] searching for operations by title
- [x] viewing list of operations within a specified period
### Reports
- [x] CRUD for Reports
- [x] creating reports based on period, category, tag, etc.
- [x] creating charts for reports
### Categories
- [x] CRUD for Categories, categorizing operations in the wallet
- [x] filtering the list of operations by categories
### Tags
- [x] CRUD for Tags, tagging operations
- [x] filtering the list of operations by tags

## Users

### Guest user:
* Logging in
* Registering

### Logged in user:
Guest user +
* Editing their own information, deleting their account, editing their password
* Creating their own wallets, recording operations
* Creating their own reports, categories, and tags, and editing them.
* Users cannot delete reports, categories, and tags created by other people.
* User can create and edit their own reports, categories, and tags visible only to them.

### Administrator:
Logged in user +
* Managing other users 
  * changing passwords, 
  * changing information
  * changing roles 
  * revoking permissions
  * inability to revoke admin permissions from the last administrator 
  * blocking user accounts
* Administrator does not have access to the wallets, transactions, and reports of other users.
* Basic categories and tags created by the administrator are visible to all users, only the administrator can modify them.

## Database schema
![screenshot](./database.png)

## Docker Symfony Starter Kit

Starter kit is based on [The perfect kit starter for a Symfony 4 project with Docker and PHP 7.2](https://medium.com/@romaricp/the-perfect-kit-starter-for-a-symfony-4-project-with-docker-and-php-7-2-fda447b6bca1).

## What is inside?

* Apache 2.4.25 (Debian)
* PHP 8.1 FPM
* MySQL 8.0.x (5.7)
* NodeJS LTS (latest)
* Composer
* Symfony CLI
* xdebug
* djfarrelly/maildev

## Requirements

* Install [Docker](https://www.docker.com/products/docker-desktop) and [Docker Compose](https://docs.docker.com/compose/install) on your machine

## Installation

in your `host` file.

* Run `build-env.sh` (or `build-env.ps1` on Windows box)

* Enter the PHP container:

```bash
docker-compose exec php bash
```

* To install Symfony LTS inside container execute:

```bash
cd app
rm .gitkeep
git config --global user.email "you@example.com"
symfony new ../app --full --version=lts
chown -R dev.dev *
```

## Container URLs and ports

* Project URL

```bash
http://localhost:8000
```

or

```bash
http://symfony.local:8000
```

* MySQL

    * inside container: host is `mysql`, port: `3306`
    * outside container: host is `localhost`, port: `3307`
    * passwords, db name are in `docker-compose.yml`

* djfarrelly/maildev i available from the browser on port `8001`

* xdebug i available remotely on port `9000`

* Database connection in Symfony `.env` file:
```yaml
DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony?serverVersion=5.7
```

## Useful commands

* `docker-compose up -d` - start containers
* `docker-compose down` - stop contaniners
* `docker-compose exec php bash` - enter into PHP container
* `docker-compose exec mysql bash` - enter into MySQL container
* `docker-compose exec apache bash` - enter into Apache2 container
