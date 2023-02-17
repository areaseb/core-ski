<?php

namespace Areaseb\Core\Models\Fe;

use Areaseb\Core\Models\{Company, Cost, Exemption, Expense, Invoice, Media, Product, Setting};

class EuVat
{
    public function cleanVat($piva, $nazione)
    {
        if(strpos($piva, $nazione) !== false)
        {
            $piva = substr($piva, 2);
        }
        $piva = $this->clean($piva);

        if($nazione == 'AT')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.'U'.str_pad($block, 8, '0', STR_PAD_LEFT);
        }
        if($nazione == 'BE')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 10, '0', STR_PAD_LEFT);
        }
        if($nazione == 'BG')
        {
            $block = $this->cleanLetters($piva);
            if(strlen($piva) == 9)
            {
                return $nazione.str_pad($block, 9, '0', STR_PAD_LEFT);
            }
            elseif(strlen($piva) == 10)
            {
                return $nazione.str_pad($block, 10, '0', STR_PAD_LEFT);
            }
        }
        if($nazione == 'CY')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 9, '0', STR_PAD_LEFT).substr($piva, -1, 1);;
        }
        if($nazione == 'CZ')
        {
            $block = $this->cleanLetters($piva);
            if(strlen($piva) == 9)
            {
                return $nazione.str_pad($block, 9, '0', STR_PAD_LEFT);
            }
            elseif(strlen($piva) == 10)
            {
                return $nazione.str_pad($block, 10, '0', STR_PAD_LEFT);
            }
            elseif(strlen($piva) == 8)
            {
                return $nazione.str_pad($block, 8, '0', STR_PAD_LEFT);
            }
        }
        if($nazione == 'DE')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 9, '0', STR_PAD_LEFT);
        }
        if($nazione == 'DK')
        {
            $block = $this->cleanLetters($piva);
            $arr = str_split($block, 2);
            return 'DK'.implode(' ', $arr);
        }
        if($nazione == 'EE')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 9, '0', STR_PAD_LEFT);
        }
        if($nazione == 'EL')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 9, '0', STR_PAD_LEFT);
        }
        if($nazione == 'ES')
        {
            $block = $this->cleanLetters($piva);
            $first = substr($piva, 0, 1);
            $last = substr($piva, -1, 1);
            if(is_numeric($last))
            {
                $last = '';
            }
            return $nazione.$first.str_pad($block, 7, '0', STR_PAD_LEFT).$last;
        }
        if($nazione == 'FI')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 8, '0', STR_PAD_LEFT);
        }
        if($nazione == 'FR')
        {
            $block = $this->clean($piva);
            $block = $this->cleanLetters($block);
            $first = substr($piva, 0, 2);
            return $nazione.$first." ".str_pad($block, 9, '0', STR_PAD_LEFT);
        }
        if($nazione == 'HR')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 11, '0', STR_PAD_LEFT);
        }
        if($nazione == 'HU')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 8, '0', STR_PAD_LEFT);
        }
        if($nazione == 'IE')
        {
            if(strlen($piva) == 8){

            	$first = substr($piva, 0, 2);
            	$last = $this->cleanNumbers(substr($piva, -1, 1));
            	$block = $this->cleanLetters(substr($piva, 2, -1));
	            return $nazione.$first.str_pad($block, 5, '0', STR_PAD_LEFT).$last;

            } elseif(strlen($piva) == 9){

            	$block = $this->cleanLetters($piva);
	            return $nazione.str_pad($block, 7, '0', STR_PAD_LEFT)."WI";
            }
        }
        if($nazione == 'IT')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 11, '0', STR_PAD_LEFT);
        }
        if($nazione == 'LT')
        {
            if(strlen($piva) == 9){

            	$block = $this->cleanLetters($piva);
	            return $nazione.str_pad($block, 9, '0', STR_PAD_LEFT);

            } elseif(strlen($piva) == 12){

            	$block = $this->cleanLetters($piva);
	            return $nazione.str_pad($block, 12, '0', STR_PAD_LEFT);
            }
        }
        if($nazione == 'LU')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 8, '0', STR_PAD_LEFT);
        }
        if($nazione == 'LV')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 11, '0', STR_PAD_LEFT);
        }
        if($nazione == 'MT')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 8, '0', STR_PAD_LEFT);
        }
        if($nazione == 'NL')
        {
            if(strlen($piva) == 12){
            	return $nazione.$piva;
            }
        }
        if($nazione == 'PL')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 10, '0', STR_PAD_LEFT);
        }
        if($nazione == 'PT')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 9, '0', STR_PAD_LEFT);
        }
        if($nazione == 'RO')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 10, '0', STR_PAD_LEFT);
        }
        if($nazione == 'SE')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 12, '0', STR_PAD_LEFT);
        }
        if($nazione == 'SI')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 8, '0', STR_PAD_LEFT);
        }
        if($nazione == 'SK')
        {
            $block = $this->cleanLetters($piva);
            return $nazione.str_pad($block, 10, '0', STR_PAD_LEFT);
        }
        if($nazione == 'XI')
        {
            if(strlen($piva) == 11){

            	$block = $this->cleanLetters($piva);
            	$first = str_pad(substr($block, 0, 3), 3, '0', STR_PAD_LEFT);
            	$second = str_pad(substr($block, 3, 7), 4, '0', STR_PAD_LEFT);
            	$last = str_pad(substr($block, 7, 9), 2, '0', STR_PAD_LEFT);
	            return $nazione.$first." ".$second." ".$last;

            } elseif(strlen($piva) == 14){

            	$block = $this->cleanLetters($piva);
            	$first = str_pad(substr($block, 0, 3), 3, '0', STR_PAD_LEFT);
            	$second = str_pad(substr($block, 3, 7), 4, '0', STR_PAD_LEFT);
            	$third = str_pad(substr($block, 7, 9), 2, '0', STR_PAD_LEFT);
            	$last = str_pad(substr($block, 9, 12), 3, '0', STR_PAD_LEFT);
	            return $nazione.$first." ".$second." ".$third." ".$last;

            } elseif(strlen($piva) == 5){

            	$block = $this->cleanLetters($piva);

            	if(substr($piva, 0, 2) == "GD"){
	            	return $nazione."GD".str_pad($block, 3, '0', STR_PAD_LEFT);
	            } elseif(substr($piva, 0, 2) == "HA"){
	            	return $nazione."HA".str_pad($block, 3, '0', STR_PAD_LEFT);
	            }

            }
        }
    }

    function clean($string)
    {
        $string = strtoupper($string);
        $string = str_replace('-', '', $string);
        $string = str_replace(' ', '', $string);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    function cleanLetters($string)
    {
        return preg_replace('/[^0-9\-]/', '', $string);
    }

    function cleanNumbers($string)
    {
        return preg_replace('/[^A-Za-z\-]/', '', $string);
    }
}
