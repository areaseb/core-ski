<?php

namespace Areaseb\Core\Models\Fe;

use \Log;
use \File;
use \Exception;
use \Storage;
use \SimpleXMLElement;
use Areaseb\Core\Models\Fe\Primitive;
use Areaseb\Core\Models\{Media, Setting};

class Xml extends Primitive
{
    public $setting;

    public function __construct($setting)
    {
        $this->connettore = $setting->connettore;
        $this->template_fattura_xml = "/invoice-".str_slug(strtolower($setting->connettore)).".xml";
    }

	public function createXml()
	{
    	return new SimpleXMLElement(File::get( __DIR__ . $this->template_fattura_xml));
	}

    public function createSimpleXml($content)
    {
    	return new SimpleXMLElement($content);
    }

    public function saveXml($template, $folder, $model)
	{
        $dt = $template->FatturaElettronicaHeader->DatiTrasmissione;
        $filename = $dt->IdTrasmittente->IdPaese . $dt->IdTrasmittente->IdCodice . '_' . $dt->ProgressivoInvio . '.xml';
        $path = storage_path('app/public/fe/'.$folder.'/'.$this->dataFromXml($template)->format('Y').'/'.$filename);

        if( file_put_contents($path, $template->asXML()) !== false )
        {
            if(!$model->media()->xml()->exists())
            {
                Media::create([
                    'description' => 'Fattura XML '.$model->numero,
                    'mime' => 'doc',
                    'filename' => $this->dataFromXml($template)->format('Y').'/'.$filename,
                    'mediable_id' => $model->id,
                    'mediable_type' => get_class($model),
                    'media_order' => 1,
                    'size' => 4
                ]);
            }
            return $path;;
        }

        return false;
    }


}
