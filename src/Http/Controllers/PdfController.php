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

        if(is_null(Setting::validSmtp(0)))
        {
            return "Imposta il server di posta e ripeti l'operazione";
        }


        //$mailer = app()->makeWith('custom.mailer', Setting::smtp(0));
        //$dsn = 'smtp://'.Setting::smtp(0)['MAIL_USERNAME'].':'.Setting::smtp(0)['MAIL_PASSWORD'].'@'.Setting::smtp(0)['MAIL_HOST'].':'.Setting::smtp(0)['MAIL_PORT'];  
        //Mail::mailer($dsn);

        $setting = Setting::emailFatture();
        
        if( $invoice->company && isset($setting[$invoice->company->lang]))
        {
            $locale = $invoice->company->lang;
        }
        elseif( $invoice->contact_id && isset($setting[$invoice->contact($invoice->contact_id)->lingua]) )
        {
        	$locale =  $invoice->contact($invoice->contact_id)->lingua;
        }
        else
        {
            $locale = 'en';
        }

		if( $invoice->company )
        {
            $nome_az = $invoice->company->rag_soc;
            $email_az = $invoice->company->invoice_email;
        }
        elseif( $invoice->contact_id )
        {
        	$nome_az = $invoice->contact($invoice->contact_id)->fullname;
        	$email_az = $invoice->contact($invoice->contact_id)->email;
        }
        
        $content = str_replace('%%%nome_azienda%%%', $nome_az, $setting[$locale]);

        if($invoice->media()->pdf()->exists() == false)
        {
            $pdf = $this->createInvoicePdf($invoice);
            $filename = 'Fattura_' . $invoice->numero . '_del_' . $invoice->data->format('d.m.Y') . '_' . str_replace(' ', '-', $nome_az) . '.pdf';
            if (file_exists(storage_path('app/public/fe/pdf/inviate/'.$filename)))
	        {
	            unlink(storage_path('app/public/fe/pdf/inviate/'.$filename));
	        }
            $pdf->save(storage_path('app/public/fe/pdf/inviate/'.$filename));
          
            $mediable_type = 'Areaseb\Core\Models\\' . $invoice->class;
            $order = Media::getMediaOrder($mediable_type, $invoice->id);
            
            Media::create([
                'description' => 'Fattura ' . $invoice->numero . ' del ' . $invoice->data->format('d.m.Y') . ' ' . $nome_az,
                'mime' => 'doc',
                'filename' => $filename,
                'mediable_id' => $invoice->id,
                'mediable_type' => $mediable_type,
                'media_order' => $order,
                'size' => Storage::disk('public')->size('fe/pdf/inviate/'.$filename)
            ]);
            
        }
        
    	$filename = $invoice->media()->pdf()->first()->filename;
    	$file = storage_path('app/public/fe/pdf/inviate/'.$filename);
                  
        $data = array(
            'setting' => Setting::base(),
            'content' => $content,
            'email' =>  $email_az,
            'title' => $setting[$locale.'_title'],
            'subject' => $setting[$locale.'_subject'],
            "file" => $file,
            "name" => $filename             
        );


        config()->set('mail.host', Setting::smtp(0)['MAIL_HOST']);
        config()->set('mail.port', Setting::smtp(0)['MAIL_PORT']);
        config()->set('mail.encryption', Setting::smtp(0)['MAIL_ENCRYPTION']);
        config()->set('mail.username', Setting::smtp(0)['MAIL_USERNAME']);
        config()->set('mail.password', Setting::smtp(0)['MAIL_PASSWORD']);


        if($invoice->tipo == 'F'){
			Mail::send('areaseb::emails.invoices.content-mail',$data, function ($message) use ($data)
	        {
	            $message->to($data['email'])
	            		->bcc(Setting::base()->email)
	                    ->subject($data['subject'])
	                    ->from(Setting::smtp(0)['MAIL_FROM_ADDRESS'])
	                    ->attach($data['file'], [
	                        'as' => $data['name'],
	                        'mime' => 'application/pdf',
	                    ]);
	        });
		} else {
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
		}


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
        if(!$filename){
        	$filename = 'Fatt_1_del_04.02.2021_AZIENDA-ITALIANA.pdf';
        }
        $file = 'fe/pdf/';
        $file .= ($model->class == 'Invoice') ? 'inviate' : 'ricevute';
        $file .= '/' . $filename;

        $fileWithPath = storage_path('app/public/'.$file);
        //dd($fileWithPath);
        
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

        if( $model->media()->where('filename', 'like', $filename)->count() > 1 )
        {
            $model->media()->where('filename', 'like', $filename)->orderBy('created_at', 'ASC')->first()->delete();
        }


        return true;
    }

    private function getTitle($xml = null, $model)
    {
        if($model->class == 'Invoice')
        {	
        	$pre = '';
        	if($model->tipo == 'F'){
        		$pre = 'Fatt';
        	} elseif($model->tipo == 'R'){
        		$pre = 'Ric';
        	} elseif($model->tipo == 'A'){
        		$pre = 'Nota Acc';
        	}
            if($model->company != null)
                return $pre . '_' . $model->numero . '_del_' . $model->data->format('d.m.Y') . '_' . strtoupper( str_slug($model->company->rag_soc) ) .'.pdf';
            else{
                $contact = $model->contact($model->contact_id);
                $lbl = $contact->nome.'_'.$contact->cognome;
                return $pre . '_' . $model->numero . '_del_' . $model->data->format('d.m.Y') . '_' . strtoupper( str_slug($lbl) ) .'.pdf';
            }

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
        		->setOption('margin-bottom', '10mm')
                ->setOption('margin-top', '5mm')
                ->setOption('margin-right', '5mm')
                ->setOption('margin-left', '5mm')
                ->setOption('encoding', 'UTF-8')
                ->setOption('footer-html', route('pdf.footer'))
                ->setOption('footer-spacing', -20)
                ->setOption('footer-font-size', 7)
             	->setOption('footer-right', 'Pagina [page] di [toPage]') ;
/*        		//->setPaper('a4')
             	//->setOrientation('portrait')
                ->setOption('header-html', route('pdf.header'))    
             	->setOption('header-right', 'Pagina [page] di [toPage]')        	
                ->setOption('header-font-size', 8)
                //->setOption('header-spacing', 40)
                ->setOption('margin-top', '40mm')
                ->setOption('margin-bottom', '30mm')
                ->setOption('margin-right', '5mm')
                ->setOption('margin-left', '5mm')
                ->setOption('footer-html', route('pdf.footer')) 
                ->setOption('footer-font-size', 8)
                //->setOption('footer-spacing', 30)
                ->setOption('encoding', 'UTF-8');*/

        /*if($this->addToDbAndSave($pdf, $title, $invoice))
        {
            return $pdf;
        }*/
        return $pdf;
    }


}
