# [Unofficial] Számlázz.hu - Számla Agent PHP API

**Nem hivatalos kiadás**, a hivatalos verzió itt érhető el: https://docs.szamlazz.hu/php

## Akkor miért?

Nem szeretném a projektbe "másolni" ezt a külső függőséget, viszont Composeres telepíthető csomagja nincs, ezért létrehoztam.

## Hogyan?

Szinte változatlanul, azért szinte, mert a PSR-4 kompatibilitás miatt a könyvtárneveket meg kell változtatni. Erre készült egy egyszerű PHP script ami az egészet elvégzi: `szamla-agent-update.php` ([szamla-agent-update.php](./szamla-agent-update.php)) és frissíti ha új verzió jönne ki. Kimenete pl:

```shell
# php szamla-agent-update.php 
Hivatalos dokumentacio letoltese es `PHPApiAgent-X.Y.Z.zip` letoltes link kiszedese...
URL to filename: `/assets/files/PHPApiAgent-2.10.19-ae230e82fc3b1443195e91441a4f20c8.zip` -> `PHPApiAgent-2.10.19-ae230e82fc3b1443195e91441a4f20c8.zip`
Saved to local: `PHPApiAgent-2.10.19-ae230e82fc3b1443195e91441a4f20c8.zip`
Regi `./szamlaagent` konyvtar torlese
Kicsomagolas: `PHPApiAgent-2.10.19-ae230e82fc3b1443195e91441a4f20c8.zip`
PSR-4 konyvtarnev javitasok:
./szamlaagent/src/szamlaagent -> ./szamlaagent/src/SzamlaAgent
./szamlaagent/src/SzamlaAgent/waybill -> ./szamlaagent/src/SzamlaAgent/Waybill
./szamlaagent/src/SzamlaAgent/response -> ./szamlaagent/src/SzamlaAgent/Response
./szamlaagent/src/SzamlaAgent/ledger -> ./szamlaagent/src/SzamlaAgent/Ledger
./szamlaagent/src/SzamlaAgent/item -> ./szamlaagent/src/SzamlaAgent/Item
./szamlaagent/src/SzamlaAgent/header -> ./szamlaagent/src/SzamlaAgent/Header
./szamlaagent/src/SzamlaAgent/document -> ./szamlaagent/src/SzamlaAgent/Document
./szamlaagent/src/SzamlaAgent/Document/invoice -> ./szamlaagent/src/SzamlaAgent/Document/Invoice
./szamlaagent/src/SzamlaAgent/Document/receipt -> ./szamlaagent/src/SzamlaAgent/Document/Receipt
./szamlaagent/src/SzamlaAgent/creditnote -> ./szamlaagent/src/SzamlaAgent/CreditNote
DONE
```

## PHP verzió

A `SzamlaAgent_PHP_API_v2.10.pdf` ([SzamlaAgent_PHP_API_v2.10.pdf](https://github.com/Benjaminhu/szamlazz-php-api/blob/2.10.18/szamlaagent/docs/SzamlaAgent_PHP_API_v2.10.pdf)) doksiból: 

> A Számla Agent PHP API használatához szükséges minimum PHP verzió: 5.6.0.

## Alternatív composer-es beállítás

Az alábbi megoldási javaslatot **@szepeviktor** küldte ([Issue #1](https://github.com/Benjaminhu/szamlazz-php-api/issues/1)), köszönet érte! A projektünkben lévő `composer.json`-ben megadható az alábbi kiegészítéssel a zip file hivatkozás ([vonatkozó composer dokumentáció "Package"](https://getcomposer.org/doc/05-repositories.md#package-2)).

```json
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "szamlazzhu/php-sdk",
                "version": "2.10.19",
                "dist": {
                    "url": "https://docs.szamlazz.hu/assets/files/PHPApiAgent-2.10.19-ae230e82fc3b1443195e91441a4f20c8.zip",
                    "type": "zip"
                },
                "autoload": {
                    "classmap": [
                        "src/szamlaagent/"
                    ]
                }
            }
        }
    ],
    "require": {
        "szamlazzhu/php-sdk": "^2.10"
    }
```
