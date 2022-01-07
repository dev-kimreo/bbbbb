<?php

namespace App\Services;

use App\Libraries\StringLibrary;
use App\Models\ScriptRequest;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Str;

final class ComponentRenderingService
{
    public static function procTemplate(string $s): string
    {
        return StringLibrary::removeSpace($s);
    }

    /**
     * @throws SourceException
     */
    public static function procStyle(string $s): string
    {
        // change
        $parsed = (new Parser($s))->parse();
        $render = '';

        foreach($parsed->getContents() as $oRule) {
            if($oRule instanceof DeclarationBlock) {
                $render .= $oRule->render(OutputFormat::create());
            } else if ($oRule instanceof AtRuleBlockList) {
                if ($oRule->atRuleName() == 'media') {
                    // set class name
                    if ($oRule->atRuleArgs() == '(min-width: 1025px)') {
                        $className = '.DAV-preview__shop--pc ';
                    } else {
                        if ($oRule->atRuleArgs() == '(max-width: 1024px)') {
                            $className = '.DAV-preview__shop--mobile ';
                        } else {
                            continue;
                        }
                    }

                    foreach ($oRule->getContents() as $v) {
                        //selector?: string, style?: string, index?: number
                        $selector = [];
                        foreach ($v->getSelectors() as $v2) {
                            $selector[] = $className . $v2;
                        }
                        $v->setSelectors($selector);
                        $render .= $v->render(OutputFormat::create());
                    }
                }
            }
        }

        // remove white space and return
        return StringLibrary::removeSpace($render);
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
        $comp = $data->component->usableVersion()->first();

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
            
            shadowRoot.innerHTML = `' . $comp->template . '<style>' . $comp->style . '</style>`;            
            
            (function(document) {        
                ' . $comp->script . '
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
