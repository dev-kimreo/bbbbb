<?php

namespace App\Services;

use App\Models\ScriptRequest;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Str;

final class ComponentRenderingService
{
    public static function procTemplate(string $s): string
    {
        return self::removeSpace($s);
    }

    public static function procStyle(string $s): string
    {
        return self::removeSpace($s);
    }

    public static function getScript(string $hash): string
    {
        $data = ScriptRequest::query()->where('hash', $hash)->firstOrFail();

        $scr = 'export function render(document, compOpt) {
            ' . $data->component->usableVersion()->first()->script . '
        };';

        return self::removeSpace($scr);
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
