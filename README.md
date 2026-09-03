# Interview-webshop

Een Laravel-applicatie met [Lunar](https://lunarphp.com), een headless
e-commercepakket voor Laravel.

Deze README helpt je met de omgeving: opstarten, testen, en weten waar
jouw code ophoudt en het pakket begint. Ze helpt je bewust *niet* met het
domein — hoe Lunar dingen modelleert zoek je zelf uit, en dat hoort bij
de opdracht.

De opdracht staat in [`OPDRACHT.md`](OPDRACHT.md).

## Opstarten

Je hebt alleen [DDEV](https://ddev.com) nodig. Geen PHP, geen MySQL, geen
Composer op je eigen machine.

```bash
ddev start
ddev composer install
ddev setup
ddev launch
```

`ddev setup` richt alles in: migraties, Lunar, demodata, de vaste logins
en de frontend-build. Je kunt hem later opnieuw draaien om met een schone
lei te beginnen — hij wist wel alles wat in de database stond.

De eerste keer duurt dit een paar minuten. Daarna:

| | |
|---|---|
| Winkel | <https://laravel-lunar-tech-interview.ddev.site> |
| Adminpaneel | <https://laravel-lunar-tech-interview.ddev.site/lunar> |

Inloggen:

| Rol | E-mail | Wachtwoord |
|---|---|---|
| Klant | `klant@interview.test` | `password` |
| Beheerder | `admin@interview.test` | `password` |

Opnieuw beginnen met verse data:

```bash
ddev setup
```

## Tests draaien

```bash
ddev artisan test
```

Dertig tests, ongeveer zeven seconden. Eén bestand filteren:

```bash
ddev artisan test --filter=CartTotalsTest
```

De tests draaien op een aparte database (`testing`), dus de demo-data in
de winkel blijft staan als je ze draait.

## Styling aanpassen

CSS en JavaScript gaan door Vite. Pas je iets aan in `resources/css/` of
`resources/js/`, of gebruik je een Tailwind-klasse die nog niet ergens in
de views voorkomt, dan moet je opnieuw bouwen:

```bash
ddev npm run build
```

Of laat het meekijken terwijl je werkt:

```bash
ddev npm run dev
```

## Artisan, composer, database

Alles loopt via DDEV, niet via je eigen PHP:

```bash
ddev artisan tinker
ddev composer require vendor/package
ddev mysql
ddev logs -f
```

## Waar staat wat

| Wat | Waar |
|---|---|
| Onze eigen code | `app/` |
| Onze tests | `tests/Feature/` |
| Tests van de starter kit | `tests/Unit/` |
| Seeders | `database/seeders/` |
| Configuratie van Lunar | `config/lunar/` |
| Lunar zelf | `vendor/lunarphp/` |

Lunar is een Composer-pakket. Wil je weten hoe iets werkt, dan lees je de
broncode in `vendor/lunarphp/`. Dat is hier geen laatste redmiddel maar
de normale manier van werken — de documentatie op
<https://docs.lunarphp.com> dekt lang niet alles.

## Waar deze codebase vandaan komt

Dit is de
[Lunar Livewire starter kit](https://github.com/lunarphp/livewire-starter-kit),
een referentie-implementatie van een klassieke webshop. De makers noemen
hem uitdrukkelijk niet productieklaar. Wij hebben hem opgetild naar
Laravel 13 en Lunar 1.5, er vaste inloggegevens in gezet en een paar
tests toegevoegd.

Met andere woorden: dit is echte code met echte ruwe randen, niet iets
wat voor jou is schoongepoetst. Kom je iets tegen dat raar is, dan is dat
waarschijnlijk gewoon zo.

## Als er iets stukgaat

De database is nog niet klaar bij `ddev start`. Wacht vijftien seconden
en probeer opnieuw.

Een klasse wordt niet gevonden na een `composer`-actie:

```bash
ddev composer dump-autoload
ddev artisan optimize:clear
```

Kom je er niet uit: vraag het. Vastlopen op de omgeving hoort niet bij
wat we willen meten.
