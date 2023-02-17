<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{Cost, Invoice, Media, Primitive, Setting};
use Illuminate\Http\Request;
use Areaseb\Core\Mail\SendInvoice;
use \PDF;
use \Storage;
use Illuminate\Support\Facades\Mail;

class PdfController extends Controller
{

//pdf/{model}/{id}
    public function generate($model, $id)
    {
        $type = $this->findModel($model, $id);
        if($type)
        {
            if($type->class == 'Invoice')
            {
                $pdf = $this->createInvoicePdf($type);
                if($pdf)
                {
                    return $pdf->inline();
                }

                return 'error';
            }
            elseif($type->class == 'Cost')
            {
                $filename = $type->media()->xml()->first()->filename;
                $file = storage_path('app/public/fe/ricevute/'.$filename);

                $content = file_get_contents($file);
                $xml = new \SimpleXMLElement($content);

                $title = $this->getTitle($xml, $type);
                $pdf = PDF::loadView('areaseb::pdf.costs.xmlTOpdf' ,compact('xml', 'title'))
                        ->setOption('margin-bottom', '0mm')
                        ->setOption('margin-top', '5mm')
                        ->setOption('margin-right', '5mm')
                        ->setOption('margin-left', '5mm')
                        ->setOption('encoding', 'UTF-8');

                $filename = substr($filename, 0, strrpos($filename, '.xml')).'.pdf';
                if($this->addToDbAndSave($pdf, $filename, $type))
                {
                    return $pdf->inline();
                }
                return 'error';
            }
        }
    }

//pdf/send/{id}
    public function sendInvoiceCortesia($id)
    {
        $invoice = Invoice::findOrFail($id);

        if(is_null($invoice->company->invoice_email))
        {
            return "Email mancante; Aggiungi l'email e ripeti l'operazione";
        }

        if(is_null(Setting::validSmtp(0)))
        {
            return "Impsta il server di posta e ripeti l'operazione";
        }


        //$mailer = app()->makeWith('custom.mailer', Setting::smtp(0));
        $dsn = 'smtp://'.Setting::smtp(0)['MAIL_USERNAME'].':'.Setting::smtp(0)['MAIL_PASSWORD'].'@'.Setting::smtp(0)['MAIL_HOST'].':'.Setting::smtp(0)['MAIL_PORT'];     
        $setting = Setting::emailFatture();
       
        if( isset($setting[$invoice->company->lang]) )
        {
            $locale = $invoice->company->lang;
        }
        else
        {
            $locale = 'en';
        }

        $content = str_replace('%%%nome_azienda%%%', $invoice->company->rag_soc, $setting[$locale]);

        if(!$invoice->media()->pdf()->exists())
        {
            $this->createInvoicePdf($invoice);
        }

        
       
        $filename = $invoice->media()->pdf()->first()->filename;
        $data = array(
            'setting' => $invoice->company,
            'content' => $content,
            'email' => $invoice->company->invoice_email,
            'title' => $setting[$locale.'_title'],
            'subject' => $setting[$locale.'_subject'],
            "file" => storage_path('app/public/fe/pdf/inviate/'.$filename),
            "name" => $filename             
        );

        //DEFINISCO IL MAILER IN BASE ALLA CONFIGURAZIONE SMTP SCELTA
        Mail::mailer($dsn);
        Mail::send('areaseb::emails.invoices.content-mail',$data, function ($message) use ($data)
        {
            $message->to($data['email'])
                    ->subject($data['subject'])
                    ->from(Setting::smtp(0)['MAIL_FROM_ADDRESS'])
                    ->attach($data['file'], [
                        'as' => $data['name'],
                        'mime' => 'application/pdf',
                    ]);
        });


        /*$mailer->send( new SendInvoice(
                $invoice->media()->pdf()->first()->filename,
                $invoice->company,
                $content,
                $setting[$locale.'_subject'],
                $setting[$locale.'_title'],
                Setting::base()
            )
        );*/

        return 'done';
    }

//HELPERS

    public function findModel($model, $id)
    {
        $class = Primitive::getClassFromDirectory($model, 'Areaseb\\Core\\Models');
        if (class_exists($class))
        {
            return $class::find($id);
        }
        return false;
    }

    private function isInvoice($model)
    {
        return $model->class == 'Invoice';
    }

    private function isCost($model)
    {
        return $model->class == 'Cost';
    }

    private function addToDbAndSave($pdf, $filename, $model)
    {
        $filename = 'Fatt_1_del_04.02.2021_AZIENDA-ITALIANA.pdf';
        $file = 'fe/pdf/';
        $file .= ($model->class == 'Invoice') ? 'inviate' : 'ricevute';
        $file .= '/' . $filename;

        $fileWithPath = storage_path('app/public/'.$file);
        dd($fileWithPath);
        
        if (file_exists($fileWithPath))
        {
            unlink($fileWithPath);
        }

        try
        { 
            $pdf->save($fileWithPath);
           
        }
        catch(\Exception $e)
        {
            dd($e, $fileWithPath);
        }


        if(!$model->media()->where('filename', $filename)->exists())
        {
            $mediable_type =  "Areaseb\\Core\\Models\\".$model->class;
            $order = Media::getMediaOrder($mediable_type, $model->id);
            $description = strtolower(substr($filename, 0, strrpos($filename, '.pdf')));

            Media::create([
                'description' => str_replace("_", " ", $description),
                'mime' => 'doc',
                'filename' => $filename,
                'mediable_id' => $model->id,
                'mediable_type' => $mediable_type,
                'media_order' => $order,
                'size' => Storage::disk('public')->size($file)
            ]);
        }

        if( $model->media()->pdf()->count() > 1 )
        {
            $model->media()->pdf()->orderBy('created_at', 'ASC')->first()->delete();
        }


        return true;
    }

    private function getTitle($xml = null, $model)
    {
        if($model->class == 'Invoice')
        {
            return 'Fatt_' . $model->numero . '_del_' . $model->data->format('d.m.Y') . '_' . strtoupper( str_slug($model->company->rag_soc) ) .'.pdf';
        }
        else
        {
            if($xml)
            {
                return "Costo_".str_replace("/", "_", $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero)."_del_".$xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data.".pdf";
            }

        }
        return 'todo';
    }


    private function createInvoicePdf($invoice)
    {
        $company = Setting::fe();
        $base = Setting::base();
        $title = $this->getTitle(null, $invoice);

        $pdf = PDF::loadView('areaseb::pdf.invoices.invoice', compact('invoice', 'company', 'title', 'base'))
                ->setOption('margin-bottom', '0mm')
                ->setOption('margin-top', '0mm')
                ->setOption('margin-right', '0mm')
                ->setOption('margin-left', '0mm')
                ->setOption('encoding', 'UTF-8');

        /*if($this->addToDbAndSave($pdf, $title, $invoice))
        {
            return $pdf;
        }*/
        return false;
    }


}
