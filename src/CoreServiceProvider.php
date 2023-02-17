<?php

namespace Areaseb\Core;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'areaseb');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'areaseb');

        // Publishing the routes.
        $this->registerRoutes();

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            //$this->bootForConsole();

            // Export the migration
            $this->myMigration();

            // Publishing the views.
            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/areaseb'),
            ], 'core.views');
            // Publishing the translation.
            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang'),
            ], 'core.trans');
            // Publishing the configs.
            $this->publishes([
              __DIR__.'/../config/core.php' => config_path('core.php'),
              __DIR__.'/../config/invoice.php' => config_path('invoice.php'),
              __DIR__.'/../config/fe.php' => config_path('fe.php'),
              __DIR__.'/../config/flare.php' => config_path('flare.php'),
          ], 'core.config');


        }
    }


    protected function registerRoutes()
    {
        Route::group(['middleware' => ['web', 'auth']], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');

    }




    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/core.php', 'core');

        // Register the service the package provides.
        $this->app->singleton('core', function ($app) {
            return new Core;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['core'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/core.php' => config_path('core.php'),
        ], 'core.config');

        // Publishing the views.
        // $this->publishes([
        //     __DIR__.'/../resources/views' => base_path('resources/views/vendor/areaseb'),
        // ], 'core.views');

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/areaseb'),
        ], 'core.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/areaseb'),
        ], 'core.views');*/

        // Registering package commands.
        // $this->commands([]);
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function myMigration(): void
    {

        $path = __DIR__ . '/../database/migrations/';

        $this->publishes([
/*          $path.'create_sectors_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()-1) . '_create_sectors_table.php'),
          $path.'create_companies_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()) . '_create_companies_table.php'),
          $path.'create_contacts_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+1) . '_create_contacts_table.php'),
          $path.'create_clients_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+2) . '_create_clients_table.php'),
          $path.'create_clientables_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+3) . '_create_clientables_table.php'),
          $path.'create_templates_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+4) . '_create_templates_table.php'),
          $path.'create_newsletters_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+5) . '_create_newsletters_table.php'),
          $path.'create_lists_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+6) . '_create_lists_table.php'),
          $path.'create_contact_list_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+7) . '_create_contact_list_table.php'),
          $path.'create_media_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+8) . '_create_media_table.php'),
          $path.'create_list_newsletter_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+9) . '_create_list_newsletter_table.php'),
          $path.'create_notifications_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+10) . '_create_notifications_table.php'),
          $path.'create_reports_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+11) . '_create_reports_table.php'),
          $path.'create_products_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+12) . '_create_products_table.php'),
          $path.'create_categories_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+13) . '_create_categories_table.php'),
          $path.'create_categorizables_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+14) . '_create_categorizables_table.php'),
          $path.'create_exemptions_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+15) . '_create_exemptions_table.php'),
          $path.'create_invoices_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+16) . '_create_invoices_table.php'),
          $path.'create_items_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+17) . '_create_items_table.php'),
          $path.'create_calendars_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+18) . '_create_calendars_table.php'),
          $path.'create_events_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+19) . '_create_events_table.php'),
          $path.'create_event_user_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+20) . '_create_event_user_table.php'),
          $path.'create_event_contact_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+21) . '_create_event_contact_table.php'),
          $path.'create_event_company_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+22) . '_create_event_company_table.php'),
          $path.'create_expenses_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+23) . '_create_expenses_table.php'),
          $path.'create_costs_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+24) . '_create_costs_table.php'),
          $path.'create_crons_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+25) . '_create_crons_table.php'),
          $path.'create_settings_table.php.stub' => database_path('migrations/' . '2020_'.date('m_d_His', time()+26) . '_create_settings_table.php'),*/
          // seeds
          __DIR__ . '/../database/seeders/cities.sql' => database_path('seeders/cities.sql'),
          __DIR__ . '/../database/seeders/CitiesSeeder.php' => database_path('seeders/CitiesSeeder.php'),
          __DIR__ . '/../database/seeders/countries.sql' => database_path('seeders/countries.sql'),
          __DIR__ . '/../database/seeders/CountriesSeeder.php' => database_path('seeders/CountriesSeeder.php'),
          __DIR__ . '/../database/seeders/SettingsSeeder.php' => database_path('seeders/SettingsSeeder.php'),
          __DIR__ . '/../database/seeders/ExemptionsSeeder.php' => database_path('seeders/ExemptionsSeeder.php'),
          __DIR__ . '/../database/seeders/StarterSeeder.php' => database_path('seeders/StarterSeeder.php'),
      ], 'core.migrations');
    }

}
