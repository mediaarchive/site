<?php
namespace App\Libs;

class Functions {
    public static function text_str_word($string, $maxlen)
    {
        $len = (mb_strlen($string) > $maxlen) ? mb_strripos(mb_substr($string, 0, $maxlen), ' ') : $maxlen;
        $cutStr = $len==0 ? mb_substr($string, 0, $maxlen) : mb_substr($string, 0, $len);
        return (mb_strlen($string) > $maxlen) ? $cutStr.'...' : $cutStr;
    }

    public static function formatted_unixtime($unixtime) {
        $m = date('n', $unixtime);
        $d = date('j', $unixtime);
        $y = date('Y', $unixtime );
        $mouth = array('янв','фев','мар','апр','мая','июн','июл','авг','сен','окт','ноя','дек');
        return $d.' '.$mouth[$m-1].' '.$y;
    }
    
    public static function str_to_unixtime($str){ // перевод из 2014-10-11 в unixtime
        $rowTime  = new \DateTime($str);
        return $rowTime->format('U');
    }

    public static function format_size($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' Гб';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 1) . ' Мб';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 0) . ' Кб';
        }
        elseif ($bytes >= 1)
        {
            $bytes = $bytes . ' байт';
        }
        else
        {
            $bytes = '0 байт';
        }

        return $bytes;
    }

    /**
     * Automatically applies "p" and "br" markup to text.
     * Basically [nl2br](http://php.net/nl2br) on steroids.
     *
     *     echo Text::auto_p($text);
     *
     * [!!] This method is not foolproof since it uses regex to parse HTML.
     *
     * @param   string  $str    subject
     * @param   boolean $br     convert single linebreaks to <br />
     * @return  string
     */
    public static function auto_p($str, $br = TRUE)
    {
        // Trim whitespace
        if (($str = trim($str)) === '')
            return '';

        // Standardize newlines
        $str = str_replace(array("\r\n", "\r"), "\n", $str);

        // Trim whitespace on each line
        $str = preg_replace('~^[ \t]+~m', '', $str);
        $str = preg_replace('~[ \t]+$~m', '', $str);

        // The following regexes only need to be executed if the string contains html
        if ($html_found = (strpos($str, '<') !== FALSE))
        {
            // Elements that should not be surrounded by p tags
            $no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

            // Put at least two linebreaks before and after $no_p elements
            $str = preg_replace('~^<'.$no_p.'[^>]*+>~im', "\n$0", $str);
            $str = preg_replace('~</'.$no_p.'\s*+>$~im', "$0\n", $str);
        }

        // Do the <p> magic!
        $str = '<p>'.trim($str).'</p>';
        $str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

        // The following regexes only need to be executed if the string contains html
        if ($html_found !== FALSE)
        {
            // Remove p tags around $no_p elements
            $str = preg_replace('~<p>(?=</?'.$no_p.'[^>]*+>)~i', '', $str);
            $str = preg_replace('~(</?'.$no_p.'[^>]*+>)</p>~i', '$1', $str);
        }

        // Convert single linebreaks to <br />
        if ($br === TRUE)
        {
            $str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);
        }

        return $str;
    }

    /**
     * Converts a file size number to a byte value. File sizes are defined in
     * the format: SB, where S is the size (1, 8.5, 300, etc.) and B is the
     * byte unit (K, MiB, GB, etc.). All valid byte units are defined in
     * Num::$byte_units
     *
     *     echo Num::bytes('200K');  // 204800
     *     echo Num::bytes('5MiB');  // 5242880
     *     echo Num::bytes('1000');  // 1000
     *     echo Num::bytes('2.5GB'); // 2684354560
     *
     * @param   string  $bytes  file size in SB format
     * @return  float
     */
    public static function bytes($size)
    {
        $byte_units = array
        (
            'B'   => 0,
            'K'   => 10,
            'Ki'  => 10,
            'KB'  => 10,
            'KiB' => 10,
            'M'   => 20,
            'Mi'  => 20,
            'MB'  => 20,
            'MiB' => 20,
            'G'   => 30,
            'Gi'  => 30,
            'GB'  => 30,
            'GiB' => 30,
            'T'   => 40,
            'Ti'  => 40,
            'TB'  => 40,
            'TiB' => 40,
            'P'   => 50,
            'Pi'  => 50,
            'PB'  => 50,
            'PiB' => 50,
            'E'   => 60,
            'Ei'  => 60,
            'EB'  => 60,
            'EiB' => 60,
            'Z'   => 70,
            'Zi'  => 70,
            'ZB'  => 70,
            'ZiB' => 70,
            'Y'   => 80,
            'Yi'  => 80,
            'YB'  => 80,
            'YiB' => 80,
        );
        // Prepare the size
        $size = trim( (string) $size);

        // Construct an OR list of byte units for the regex
        $accepted = implode('|', array_keys($byte_units));

        // Construct the regex pattern for verifying the size format
        $pattern = '/^([0-9]+(?:\.[0-9]+)?)('.$accepted.')?$/Di';

        // Verify the size format and store the matching parts
        if ( ! preg_match($pattern, $size, $matches))
            return false;

        // Find the float value of the size
        $size = (float) $matches[1];

        // Find the actual unit, assume B if no unit specified
        $unit = isset($matches[2]) ? $matches[2] : 'B';

        // Convert the size into bytes
        $bytes = $size * pow(2, $byte_units[$unit]);

        return $bytes;
    }

    public static function format_money($money) {
        return money_format('%!n', $money);
    }
    
    public static function if_checkbox_checked($var){
        if(isset($var) && $var !== null && $var == 'on')
            return true;
        else
            return false;
    }

    public static function genhash($length){ // gen unique word. Takes count of letters
        $chars = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
        $numChars = strlen($chars);
        $string = '';

        for($i = 0; $i < $length; $i++)
        $string .= substr($chars, rand(1, $numChars) - 1, 1);

        return $string;
    }
}