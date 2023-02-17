<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\Media;

class MediaController extends Controller
{

//api/media/upload - POST
    public function add()
    {
        $response = Media::saveImageOrFile(request());
        return $response;
    }

//api/media/update - POST
    public function update()
    {
        $file = Media::find(request('id'));
            $file->description = request('description');
        $file->save();
        return 'descrizione aggiornata';
    }

//api/media/delete - DELETE
    public function delete()
    {
        Media::deleteMediaFromId( request('id') );
        return back()->with('message', 'Media Rimosso');
    }

//api/media/order - POST
    public function sort()
    {
        foreach ( request('media_order') as $key => $value) {
            Media::where('id', $value)->update(['media_order' => $key+1]);
        }
        return 'ordine aggiornato';
    }

//api/media/type - POST
    public function type()
    {
        $file = Media::findOrFail(request('id'));
            $file->type = request('type');
        $file->save();

        return 'tipo cambiato';
    }
}
