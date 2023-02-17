<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;
use App\Mediatypes\{MediaEditor, MediaGeneral};

class Media extends Model
{
    protected $guarded = array();

    public function mediable()
    {
        return $this->morphTo();
    }

//images/files getters
    public function getOriginalAttribute()
    {
        return asset('storage/'.$this->directory.'/original/'.$this->filename);
    }

    public function getDocAttribute()
    {
        return asset('storage/'.$this->directory.'/docs/'.$this->filename);
    }

    public function getFullAttribute()
    {
        return asset('storage/'.$this->directory.'/full/'.$this->filename);
    }

    public function getDisplayAttribute()
    {
        return asset('storage/'.$this->directory.'/display/'.$this->filename);
    }

    public function getThumbAttribute()
    {
        return asset('storage/'.$this->directory.'/thumb/'.$this->filename);
    }


//model helpers
    public function getDirectoryAttribute()
    {
        $arr = explode("\\",$this->mediable_type);
    	return str_plural( strtolower( end($arr) ) );
    }

    public function getDimensionAttribute()
    {
        return $this->width.'x'.$this->height;
    }

    public function getIsPortraitAttribute()
    {
        if($this->width > $this->height)
        {
            return false;
        }
        return true;
    }

    public function getKbAttribute()
    {
        return $this->size . ' Kb';
    }

    public function getIsFileAttribute()
    {
        if($this->mime == 'doc')
        {
            return true;
        }
        return false;
    }

    public function getWordAttribute()
    {
        $exts = ['doc', 'docx', 'dotx', 'odt', 'odtx', 'ott', 'rtf', 'txt'];
        $arr = explode('.', $this->filename);
        $ext = strtolower(end($arr));
        if(in_array($ext, $exts))
        {
            return true;
        }
        return false;
    }

    public function getExcelAttribute()
    {
        $exts = ['xls', 'xlsx', 'ods', 'odsx', 'csv'];
        $arr = explode('.', $this->filename);
        $ext = strtolower(end($arr));
        if(in_array($ext, $exts))
        {
            return true;
        }
        return false;
    }

    public function getArchiveAttribute()
    {
        $exts = ['zip', 'rar', 'gz', '7z', 'zipx'];
        $arr = explode('.', $this->filename);
        $ext = strtolower(end($arr));
        if(in_array($ext, $exts))
        {
            return true;
        }
        return false;
    }


    public function getIconAttribute()
    {
        if($this->is_file)
        {
            $arr = explode('.', $this->filename);
            $ext = strtolower(end($arr));
            if($this->word)
            {
                return '<i class="far fa-fw fa-file-word"></i>';
            }
            elseif(strpos($ext, 'pdf') !== false)
            {
                return '<i class="far fa-fw fa-file-pdf"></i>';
            }
            elseif($this->excel)
            {
                return '<i class="far fa-fw fa-file-excel"></i>';
            }
            elseif($this->archive)
            {
                return '<i class="far fa-file-archive"></i>';
            }
            else
            {
                return '<i class="far fa-fw fa-file-alt"></i>';
            }
        }
        return '<i class="far fa-fw fa-image"></i>';
    }

// SCOPES

    public function scopeXml($query)
    {
        $query = $query->where('filename', 'like', '%'.'.xml'.'%');
    }

    public function scopePdf($query)
    {
        $query = $query->where('filename', 'like', '%'.'.pdf'.'%');
    }

    public function scopeImg($query)
    {
        $query = $query->where('mime', 'image');
    }

//FUNCTIONS
//check if is image with valid extension (request->file)
    public static function isImage($file)
    {
        $imageExtensions = ['jpeg','jpg','png'];
        $ext = strtolower($file->getClientOriginalExtension());
        if ( in_array($ext, $imageExtensions) )
        {
            return true;
        }
        return false;
    }


//set numeric order to images of the same model
    public static function getMediaOrder($type, $id)
    {
        if (self::where('mediable_type', $type)->where('mediable_id', $id)->exists())
        {
            return self::where('mediable_type', $type)->where('mediable_id', $id)->orderBy('id', 'DESC')->first()->media_order+1;
        }
        return 1;
    }


//delete all media from model
    public static function deleteAllMedia($model)
    {
        if($model->media()->exists())
        {
            foreach($model->media as $file)
            {
                self::deleteMediaFromId($file->id);
            }
        }
        return 'done';
    }

//delete single media from db and server
    public static function deleteMediaFromId($id)
    {
        $media = self::find($id);
        $folders = ['/thumb/', '/original/'];
        foreach($folders as $folder)
        {
            if( file_exists ( storage_path(''.$media->directory.$folder.$media->filename) ) )
            {
                unlink( storage_path(''.$media->directory.$folder.$media->filename) );
            }
        }
        $media->delete();
        return 'done';
    }


    public static function makeNiceName($request, $ext)
    {

        $original = $request->file->getClientOriginalName();
        $original = str_replace('(','', $original);
        $original = str_replace(')','', $original);
        if( file_exists ( storage_path(''.$request->directory.'/original/'.$original) ) )
        {
            return pathinfo($original, PATHINFO_FILENAME).'-'.str_random(2).'.'.$ext;
        }

        return str_replace(" ","-", strtolower($original));
    }

    public static function makeNiceDescription($request)
    {
        $model = self::findModelFromRequest($request->mediable_type, $request->mediable_id);

        if( is_null($model) )
        {
            return $request->file->getClientOriginalName();
        }

        return $model->id . ' ' . self::getMediaOrder($request->mediable_type, $request->mediable_id);
    }

    public static function findModelFromRequest($model, $id = null)
    {
        if( is_null($id) )
        {
            $id = 1;
        }

		if ( class_exists($model) )
		{
			return $model::findOrFail($id);
		}

        return null;
    }

//save file in db and server ($request = upload request)
    public static function saveImageOrFile($request)
    {
        if ( $request->hasFile('file') )
        {
            if ( $request->file->isValid() )
            {
                $filename = self::makeNiceName($request, strtolower($request->file->getClientOriginalExtension()) );

                if ( self::isImage($request->file) )
                {
                    $request->file->storeAs('public/'.$request->directory.'/original', $filename );

                    $temp = explode("\\", $request->mediable_type);
                    $class = '\App\Mediatypes\Media'.end($temp);

                    if (class_exists($class))
                    {
                        $media = new $class;
                    }
                    else
                    {
                        $media = new MediaGeneral;
                    }


                    $media->resizeAndSaveImage($request->file, $filename, $request->directory);

                    $image = \Image::make( $request->file->getRealPath() );
                    if($request->mediable_id)
                    {
                        $media = self::create([
                            'description' => self::makeNiceDescription($request),
                            'mime' => "image",
                            'filename' => $filename,
                            'mediable_id' => $request->mediable_id,
                            'mediable_type' => $request->mediable_type,
                            'media_order' => self::getMediaOrder($request->mediable_type, $request->mediable_id),
                            'width' => $image->width(),
                            'height' => $image->height(),
                            'size' => round($image->filesize()/1000)
                        ]);
                    }
                    if(isset($media->id))
                    {
                        return $media->id;
                    }
                    return 'done';

                }

                $request->file->storeAs('public/'.$request->directory.'/docs', $filename );

                $media = self::create([
                    'description' => 'document ' . self::makeNiceDescription($request),
                    'mime' => 'doc',
                    'filename' => $filename,
                    'mediable_id' => $request->mediable_id,
                    'mediable_type' => $request->mediable_type,
                    'media_order' => self::getMediaOrder($request->mediable_type, $request->mediable_id),
                    'size' => round($request->file->getSize()/1000)
                ]);

                return $media->id;
            }
            return 'invalid file';
        }
        return 'request has no file';
    }

}
