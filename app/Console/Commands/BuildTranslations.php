<?php

namespace App\Console\Commands;

use App\Models\Exception;
use App\Models\Word;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BuildTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Init
        $wordArrays = [];
        $res = [];

        // Getting translated words from DB
        Word::with('translation.translationContents')->get()->each(function ($word) use (&$res, &$wordArrays) {
            $translation = $word->translation;
            $translation->translationContents->each(function ($content) use ($word, $translation, &$res, &$wordArrays) {
                $wordArrays[$content->lang][$word->code] = $content->value;

                $this->assignArrayByPath(
                    $res[$content->lang][$translation->linkable_type],
                    $word->code,
                    $content->value
                );
            });
        });

        // Getting translated words from DB
        Exception::with('translation.translationContents')->get()->each(function ($exception) use (&$res, $wordArrays) {
            $translation = $exception->translation;
            $translation->translationContents->each(function ($content) use ($exception, $translation, &$res, $wordArrays) {
                preg_match_all('/(\:word.[a-zA-Z\_\.]+)/', $content->value, $matched);

                if (isset($matched[1]) && count($matched[1])) {
                    foreach ($matched[1] as $k => $v) {
                        if (isset($wordArrays[$content->lang][str_replace(':word.', '', $v)])) {
                            $content->value = str_replace($v, $wordArrays[$content->lang][str_replace(':word.', '', $v)], $content->value);
                        } else {
                            $content->value = str_replace($v, str_replace(':word.', 'word.', $v), $content->value);
                        }
                    }
                }

                $this->assignArrayByPath(
                    $res[$content->lang][$translation->linkable_type],
                    $exception->code,
                    $content->value
                );
            });
        });

        // Getting the path for language resources
        $basePath = base_path();

        // Export
        foreach ($res as $lang => $v) {
            foreach ($v as $filename => $v2) {
                // Getting Path
                if (strpos($basePath, '\\') !== false) {
                    $path = $basePath . '\\resources\\lang\\' . $lang . '\\' . $filename . '.php';
                } else {
                    $path = $basePath . '/resources/lang/' . $lang . '/' . $filename . '.php';
                }

                // Getting Contents
                $comment = "/* This file is generated by artisan build:translations command at KST " . Carbon::now()->timezone('Asia/Seoul') . ".*/";
                $contents = "<?php\n" . $comment . "\nreturn ";
                $contents .= var_export($v2, true) . ";";

                file_put_contents($path, $contents);
            }
        }
    }

    // ?????????(.)?????? ????????? ???????????? ??????????????? ??????
    protected function assignArrayByPath(&$arr, $path, $value, $separator = '.')
    {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }
}
