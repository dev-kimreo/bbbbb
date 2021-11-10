<?php

namespace App\Services;

use App\Models\Components\Component;
use App\Models\ScriptRequest;
use Auth;
use Str;

final class RequestScriptUrlService
{
    public static function getScript(string $funcName, int $id): string
    {
        $req = ScriptRequest::query()->findOrFail($id);
        $scr = $req->component->usableVersion->script;

        $scr = preg_replace('/[\r\n]+', '', $scr);
        $scr = preg_replace('/[\s]+', ' ', $scr);
        $scr = preg_replace('/[\s]{2,}', ' ', $scr);

        return $funcName
            . '=function(componentOptionData){(function(document, compOpt){'
            . $scr
            . '};funcName();';
    }

    public static function generateUrl(Component $component): string
    {
        $hash = Str::random(64);
        $url = '/v1/component/script/' . $hash;

        ScriptRequest::query()->insert(
            [
                'user_id' => Auth::id(),
                'component_id' => $component->id,
                'hash' => $hash,
                'url' => $url
            ]
        );

        return $url;
    }
}
