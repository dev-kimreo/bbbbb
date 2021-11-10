<?php

namespace App\Services;

use App\Models\ScriptRequest;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Str;

final class ComponentRenderingService
{
    public static function getScript(string $funcName, string $hash): string
    {
        $data = ScriptRequest::query()->where('hash', $hash)->firstOrFail();

        $scr = $funcName . '=function(componentOptionData){
            (function(document, compOpt){
                ' . $data->component->usableVersion()->first()->script . '
            })(
                document.querySelector("#qpick_component_27963").attachShadow({mode:"closed"}),
                componentOptionData
	        );
        };
        ' . $funcName . '({});';
        // TODO - componentOptionData 자료구조 입력

        $scr = preg_replace('/[\r\n]+/', '', $scr);
        $scr = preg_replace('/[\s]+/', ' ', $scr);
        return preg_replace('/[\s]{2,}/', ' ', $scr);
    }

    public static function procTemplate(string $template): string
    {
        return '<div id="qpick_component_27963">' . self::removeSpace($template) . '</div>';
    }

    public static function procStyle(string $style): string
    {
        return self::removeSpace(
            preg_replace(
                '/(}?)([^{}]+){/',
                '$1' . '#qpick_component_27963 ' . '$2{',
                $style
            )
        );
    }

    public static function generateUrl(Model $component): string
    {
        $hash = Str::random(64);
        $url = request()->root() . '/v1/component/script/' . $hash;

        $model = ScriptRequest::create(
            [
                'hash' => $hash,
                'url' => $url
            ]
        );
        $model->user()->associate(Auth::user());
        $model->component()->associate($component->component()->first());
        $model->requestable()->associate($component);
        $model->save();

        return $url;
    }

    protected static function removeSpace(string $s): string
    {
        $s = preg_replace('/[\r\n]+/', '', trim($s));
        $s = preg_replace('/[\s]+/', ' ', $s);
        return preg_replace('/[\s]{2,}/', ' ', $s);
    }
}
