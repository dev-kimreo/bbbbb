<?php

namespace App\Services;

use App\Libraries\StringLibrary;
use App\Models\ScriptRequest;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Str;

final class ComponentRenderingService
{
    public static function procTemplate(string $s): string
    {
        return StringLibrary::removeSpace($s);
    }

    public static function procStyle(string $s): string
    {
        return StringLibrary::removeSpace($s);
    }

    public static function getScript(string $hash): string
    {
        $data = ScriptRequest::query()->where('hash', $hash)->firstOrFail();

        $scr = 'export function render(shadowRoot, compOpt) {
            let arrMethod = [
                "createElement",
                "createTextNode",
                "createDocumentFragment"
            ];
                        
            for(const fn of arrMethod) {
                shadowRoot[fn] = function(v = null){
                    return document[fn](v)
                };
            }
            
            (function(document) {        
                ' . $data->component->usableVersion()->first()->script . '
            })(shadowRoot);
        };';

        return StringLibrary::removeSpace($scr);
    }

    public static function getJsonp(string $hash, string $callback): string
    {
        $data = ScriptRequest::query()->where('hash', $hash)->firstOrFail();

        $scr = $callback . '(function(shadowRoot, compOpt) {
            let arrMethod = [
                "createElement",
                "createTextNode",
                "createDocumentFragment"
            ];
                        
            for(const fn of arrMethod) {
                shadowRoot[fn] = function(v = null){
                    return document[fn](v)
                };
            }
            
            (function(document) {        
                ' . $data->component->usableVersion()->first()->script . '
            })(shadowRoot);
        });
        ';

        //return StringLibrary::removeSpace($scr);
        return $scr;
    }

    public static function generateUrl(Model $component): string
    {
        $hash = Str::random(64);
        $url = request()->root() . '/v1/component/script/' . $hash . '.js';

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
}
