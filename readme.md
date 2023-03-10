
## Installation

Via Composer

``` bash
$ composer require areaseb/core-ski
```

## Usage

``` bash
$ composer require Areaseb/Core-ski
$ php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
$ php artisan vendor:publish --provider="Areaseb\Core\CoreServiceProvider" --tag="core.migrations"
$ php artisan vendor:publish --provider="Areaseb\Core\CoreServiceProvider" --tag="core.trans"
$ php artisan vendor:publish --provider="Areaseb\Core\CoreServiceProvider" --tag="core.config"
$ php artisan storage:link
$ php artisan queue:table

$ php artisan db:seed --class=CitiesSeeder
$ php artisan db:seed --class=CountriesSeeder


$ composer dump-autoload

$ php artisan migrate
$ php artisan db:seed --class=ExemptionsSeeder
$ php artisan db:seed --class=SettingsSeeder
$ php artisan db:seed --class=StarterSeeder

```
