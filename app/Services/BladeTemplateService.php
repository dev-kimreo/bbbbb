<?php

namespace App\Services;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler as LaravelBladeCompiler;
use Illuminate\View\Factory as ViewFactory;

/*
 * Reference from
 * https://stackoverflow.com/questions/16891398/is-there-any-way-to-compile-a-blade-template-from-a-string
 */
class BladeTemplateService extends LaravelBladeCompiler
{
    /**
     * Compile blade template with passing arguments.
     *
     * @param string $value HTML-code including blade
     * @param array $args Array of values used in blade
     * @return string
     * @throws Exception
     */
    public function compileWiths(string $value, array $args = []): string
    {
        $generated = parent::compileString($value);

        ob_start() and extract($args, EXTR_SKIP);

        // We'll include the view contents for parsing within a catcher
        // so we can avoid any WSOD errors. If an exception occurs we
        // will throw it out to the exception handler.
        try
        {
            $__env = app(ViewFactory::class);
            eval('?>'.$generated);
        }

        // If we caught an exception, we'll silently flush the output
        // buffer so that no partially rendered views get thrown out
        // to the client and confuse the user with junk.
        catch (Exception $e)
        {
            ob_get_clean(); throw $e;
        }

        return ob_get_clean();
    }

    public static function instance(): static
    {
        return new static(new Filesystem, sys_get_temp_dir());
    }
}
