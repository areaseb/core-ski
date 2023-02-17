<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Editor, Media, Template};
use \Storage;

class EditorController extends Controller
{

//template-builder - GET
    public function editor()
    {
        $elements = asset( 'editor/elements-create.json' );
        $update = false;
        $page = asset( 'editor/template-load-page.html' );
        return view('areaseb::core.editor.template-builder', compact('elements', 'update', 'page'));
    }

//new-template-builder - GET
    public function createTemplateBuilder()
    {
        return view('areaseb::core.editor.create-template-builder');
    }

//edit-template-builder/{id} - GET
    public function editTemplateBuilder($id)
    {
        return view('areaseb::core.editor.edit-template-builder', compact('id'));
    }

//template-builder/{id} - GET
    public function editorWithTemplate($id)
    {
        $elements = asset( 'editor/elements-edit.json' );
        $update = true;
        $template = Template::find($id);
        $page = $template->url;
        return view('areaseb::core.editor.template-builder', compact('elements', 'update', 'template', 'page'));
    }

//editor/elements/{slug} - GET
    public function show($slug)
    {
        return view('areaseb::core.editor.elements.'.$slug);
    }

//editor/images - GET
    public function images()
    {
        $thumb = Storage::disk('public')->files('editor/thumb');
        $display = Storage::disk('public')->files('editor/250x150');
        $full = Storage::disk('public')->files('editor/600x200');
        $original = Storage::disk('public')->files('editor/original');
        $half = Storage::disk('public')->files('editor/350x150');

        $files = []; $captions = [];
        foreach($thumb as $key => $image)
        {
            if(isset($display[$key]))
            {
                $files[] = $display[$key];
                $captions[] = '250x150';
            }
            if(isset($full[$key]))
            {
                $files[] = $full[$key];
                $captions[] = '600x200';
            }
            if(isset($half[$key]))
            {
                $files[] = $half[$key];
                $captions[] = '350x150';
            }
            if(isset($original[$key]))
            {
                $files[] = $original[$key];
                $captions[] = 'original';
            }
        }

        $response = [];
        $response['code'] = 0;
        $response['files'] = $files;
        $response['captions'] = $captions;
        $response['directory'] = asset('storage')."/";
        return $response;
    }

//editor/upload - POST
    public function upload(Request $request)
    {
        return Media::saveImageOrFile($request);
    }

//editor/delete - POST
    public function delete(Request $request)
    {
        if( file_exists(storage_path('app/public/'.$request->filename) ) )
        {
            unlink(storage_path('app/public/'.$request->filename));
            return 'done';
        }
        return 'file not found';
    }

}
