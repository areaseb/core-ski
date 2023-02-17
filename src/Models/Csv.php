<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Csv extends Model
{

    //find delimiter in csv file
    public static function getDelimiter($csvFile)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle);

        foreach ($delimiters as $delimiter => &$count)
        {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

    //check if firstLine is a header
    public static function hasHeader($csvFile)
    {
        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle);

        if(strpos($firstLine, "@") !== false)
        {
            return false;
        }

        return true;
    }

    //get the header
    public static function getHeader($csvFile)
    {
        $delimiter = self::getDelimiter($csvFile);
        $headerValues = [];

        if (($handle = fopen($csvFile, "r")) !== FALSE)
        {
           while(($data = fgetcsv($handle, 1000, $delimiter)) !== false)
           {
                 $headerValues = self::toUTF($data);
                 break;
           }
        }

        return $headerValues;
    }

    //return the first 4 lines
    public static function peek($csvFile)
    {
        $delimiter = self::getDelimiter($csvFile);
        $count = 0;
        $hasHeader = self::hasHeader($csvFile);
        $peek = [];
        if (($handle = fopen($csvFile, "r")) !== FALSE)
        {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if($hasHeader && $count > 0)
                {
                    if($count <= 4)
                    {
                        $peek[$count] = self::toUTF($data);
                    }
                    else
                    {
                        break;
                    }
                }
                elseif(!$hasHeader)
                {
                    if($count <= 3)
                    {
                        $peek[$count] = self::toUTF($data);
                    }
                    else
                    {
                        break;
                    }
                }
                $count++;
            }
            fclose($handle);
        }
        return $peek;
    }

    public static function read($csvFile)
    {
        $delimiter = self::getDelimiter($csvFile);
        $count = 0;
        $hasHeader = self::hasHeader($csvFile);
        $peek = [];
        if (($handle = fopen($csvFile, "r")) !== FALSE)
        {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if($hasHeader && $count > 0)
                {
                    $peek[$count] = self::toUTF($data);
                }
                elseif(!$hasHeader)
                {
                    $peek[$count] = self::toUTF($data);
                }
                $count++;
            }
            fclose($handle);
        }
        return $peek;
    }

    public static function makeHeader($filename)
    {
        return [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];
    }

    /**
     * return string in UTF-8
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function toUTF($data)
    {
        $clean = [];
        foreach ($data as $key => $value) {

            if(mb_detect_encoding($value,['ISO-8859-1', 'Windows-1252']) == "ISO-8859-1")
            {
                $clean[] = iconv( "ISO-8859-1", "UTF-8", $value );
            }
            elseif(mb_detect_encoding($value,['ISO-8859-1', 'Windows-1252']) == "Windows-1252")
            {
                $clean[] = iconv( "Windows-1252", "UTF-8", $value );
            }
            else
            {
                $clean[] = $value;
            }
        }
        return $clean;
    }

}
