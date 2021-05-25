<?php

namespace App\Libraries;

use Str;

class StringLibrary
{
    // Escape for like query on MariaDB
    public static function escapeSql(string $v): string
    {
        $v = str_replace('%', '\\%', $v);
        $v = str_replace('_', '\\_', $v);

        return $v;
    }

    // 한국어 조사변화 from https://taegon.kim/archives/4776
    public static function convertParticle($str)
    {
        $josa = '이가은는을를과와으로';

        return preg_replace_callback(
            "/(.)\\{([{$josa}])\\}/u",
            function ($matches) use ($josa) {
                list($_, $last, $pp) = $matches;

                $pp1 = $pp2 = $pp;
                $idx = mb_strpos($josa, $pp);
                ($idx % 2) ? ($pp1 = mb_substr($josa, --$idx, 1)) : ($pp2 = mb_substr($josa, ++$idx, 1));
                $pp1 .= ($pp1 === '으') ? $pp2 : '';

                if (strlen($last) > 1) {
                    $last_ucs2 = mb_convert_encoding($last, 'UCS-2BE', 'UTF-8');
                    $code = (hexdec(bin2hex($last_ucs2)) - 16) % 28;
                } else {
                    $code = (strpos('2459', $last) > -1) ? 0 : 1;
                }

                return $last . ($code ? $pp1 : $pp2);
            },
            $str
        );
    }
}
