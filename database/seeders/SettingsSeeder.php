<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Areaseb\Core\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
     public function run()
     {

         $base = [
             "rag_soc" => "Nome azienda",
             "indirizzo" => "Via indirizzo, n",
             "cap" => "00000",
             "provincia" => "AO",
             "citta" => "Città",
             "nazione" => "Italia",
             "piva" => "0123456789",
             "cod_fiscale" => "0123456789",
             "telefono" => "",
             "email" => "",
             "sitoweb" => "",
             "banca" => "",
             "IBAN" => "",
             "SWIFT" => "",
             "default_color" => "#f68d23",
             "logo_img" => "",
             "footer_img" => "",
             "logo_fattura_img" => "",
             "marketing_cost" => "0" //come annuale poi dividere leads anno / clients / anno
         ];

         Setting::create(['model' => 'Base', 'fields' => $base]);


         $fields = [
             0 => [
                "MAIL_DRIVER" => "smtp",
                "MAIL_HOST" => "",
             	"MAIL_PORT" => "",
             	"MAIL_USERNAME" => "",
             	"MAIL_PASSWORD" => "",
             	"MAIL_ENCRYPTION" => "",
             	"MAIL_FROM_ADDRESS" => "",
             	"MAIL_FROM_NAME" => ""
             ],
             1 => [
                "MAIL_DRIVER" => "smtp",
             	"MAIL_HOST" => "",
             	"MAIL_PORT" => "",
             	"MAIL_USERNAME" => "",
             	"MAIL_PASSWORD" => "",
             	"MAIL_ENCRYPTION" => "",
             	"MAIL_FROM_ADDRESS" => "",
             	"MAIL_FROM_NAME" => ""
             ],
             2 => [
                "MAIL_DRIVER" => "smtp",
             	"MAIL_HOST" => "",
             	"MAIL_PORT" => "",
             	"MAIL_USERNAME" => "",
             	"MAIL_PASSWORD" => "",
             	"MAIL_ENCRYPTION" => "",
             	"MAIL_FROM_ADDRESS" => "",
             	"MAIL_FROM_NAME" => ""
             ]
         ];

         Setting::create(['model' => 'SMTP', 'fields' => $fields]);



         $newsletter = [
             "invia_da_email" => "",
             "default_test_email" => "",
             "default_img_logo" => "",
             "unsub_notification_email" => ""
         ];

         Setting::create(['model' => 'Newsletter', 'fields' => $newsletter]);


         $socials = [
             "facebook" => "https://www.facebook.com",
             "twitter" => null,
             "instagram" => null,
             "linkedin" => "https://www.linkedin.com"
         ];

         Setting::create(['model' => 'Social', 'fields' => $socials]);

        $fe = [
            "connettore" => "Fatture in Cloud",
            "connettore_uid" => null,
            "connettore_key" => null,
            "user_sdi" => null,
            "pwd_sdi" => null,
            "domain_sdi" => null,
            "nazione" => "IT",
            "piva" => "0123456789",
            "rag_soc" => null,
            "regime" => "RF01",
            "indirizzo" => null,
            "cap" => null,
            "citta" => null,
            "prov" => null,
            "tel" => null,
            "email"=>null,
            "web"=>null,
            "banca"=>null,
            "IBAN"=>null,
            "last_receive"=>"2021-01-01",
            "max_receive"=>"20",
            "last_sync"=>"2021-01-01",
            "max_sync"=>"20"
        ];

        Setting::create(['model' => 'Fe', 'fields' => $fe]);



        $langs = [
            0 => [
               "LANG_NAME" => "Italiano",
               "LANG_ISO" => "it",
               "LANG_LOCALE" => "IT_it",
               "LANG_ACTIVE" => "1",
            ],
            1 => [
                "LANG_NAME" => "Inglese",
                "LANG_ISO" => "en",
                "LANG_LOCALE" => "US_en",
                "LANG_ACTIVE" => "0",
            ],
            2 => [
                "LANG_NAME" => "Tedesco",
                "LANG_ISO" => "de",
                "LANG_LOCALE" => "DE_de",
                "LANG_ACTIVE" => "0",
            ],
            3 => [
                "LANG_NAME" => "Francese",
                "LANG_ISO" => "fr",
                "LANG_LOCALE" => "FR_fr",
                "LANG_ACTIVE" => "0",
            ],
            4 => [
                "LANG_NAME" => "Spagnolo",
                "LANG_ISO" => "es",
                "LANG_LOCALE" => "ES_es",
                "LANG_ACTIVE" => "0",
            ],
            5 => [
                "LANG_NAME" => "Portoghese",
                "LANG_ISO" => "pt",
                "LANG_LOCALE" => "PT_pt",
                "LANG_ACTIVE" => "0",
            ],
            6 => [
                "LANG_NAME" => "Arabo",
                "LANG_ISO" => "ar",
                "LANG_LOCALE" => "SA_ar",
                "LANG_ACTIVE" => "0",
            ],
            7 => [
                "LANG_NAME" => "Russo",
                "LANG_ISO" => "ru",
                "LANG_LOCALE" => "RU_ru",
                "LANG_ACTIVE" => "0",
            ],
            8 => [
                "LANG_NAME" => "Cinese",
                "LANG_ISO" => "zh",
                "LANG_LOCALE" => "CN_zh",
                "LANG_ACTIVE" => "0",
            ],
            9 => [
                "LANG_NAME" => "Giapponese",
                "LANG_ISO" => "ja",
                "LANG_LOCALE" => "JA_ja",
                "LANG_ACTIVE" => "0",
            ]
        ];

        Setting::create(['model' => 'Lingue', 'fields' => $langs]);
        
     }
}
