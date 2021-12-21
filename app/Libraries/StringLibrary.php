<?php

namespace App\Libraries;

use Str;

class StringLibrary
{
    // Escape for like query on MariaDB
    public static function escapeSql(string $v): string
    {
        return addcslashes($v, '%_');
    }

    // Check the string is an ISO 639-1 code.
    public static function chkIso639_1Code(string $v): bool
    {
        return in_array($v, [
            'aa', 'ab', 'ae', 'af', 'ak', 'am', 'an', 'ar', 'as', 'av',
            'ay', 'az', 'ba', 'be', 'bg', 'bh', 'bi', 'bm', 'bn', 'bo',
            'br', 'bs', 'ca', 'ce', 'ch', 'co', 'cr', 'cs', 'cu', 'cv',
            'cy', 'da', 'de', 'dv', 'dz', 'ee', 'el', 'en', 'eo', 'es',
            'et', 'eu', 'fa', 'ff', 'fi', 'fj', 'fo', 'fr', 'fy', 'ga',
            'gd', 'gl', 'gn', 'gu', 'gv', 'ha', 'he', 'hi', 'ho', 'hr',
            'ht', 'hu', 'hy', 'hz', 'ia', 'id', 'ie', 'ig', 'ii', 'ik',
            'io', 'is', 'it', 'iu', 'ja', 'jv', 'ka', 'kg', 'ki', 'kj',
            'kk', 'kl', 'km', 'kn', 'ko', 'kr', 'ks', 'ku', 'kv', 'kw',
            'ky', 'la', 'lb', 'lg', 'li', 'ln', 'lo', 'lt', 'lu', 'lv',
            'mg', 'mh', 'mi', 'mk', 'ml', 'mn', 'mr', 'ms', 'mt', 'my',
            'na', 'nb', 'nd', 'ne', 'ng', 'nl', 'nn', 'no', 'nr', 'nv',
            'ny', 'oc', 'oj', 'om', 'or', 'os', 'pa', 'pi', 'pl', 'ps',
            'pt', 'qu', 'rm', 'rn', 'ro', 'ru', 'rw', 'sa', 'sc', 'sd',
            'se', 'sg', 'si', 'sk', 'sl', 'sm', 'sn', 'so', 'sq', 'sr',
            'ss', 'st', 'su', 'sv', 'sw', 'ta', 'te', 'tg', 'th', 'ti',
            'tk', 'tl', 'tn', 'to', 'tr', 'ts', 'tt', 'tw', 'ty', 'ug',
            'uk', 'ur', 'uz', 've', 'vi', 'vo', 'wa', 'wo', 'xh', 'yi',
            'yo', 'za', 'zh', 'zu'
        ]);
    }

    // 중복된 공백문자 및 개행문자 제거
    public static function removeSpace(string $s): string
    {
        $s = preg_replace('/[\r\n]+/', '', trim($s));
        $s = preg_replace('/[\s]+/', ' ', $s);
        return preg_replace('/[\s]{2,}/', ' ', $s);
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
