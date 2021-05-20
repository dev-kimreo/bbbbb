<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class BuildApiErrorsResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:apiErrorsResponse';

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
     *
     * @return int
     */
    public function handle()
    {
        $lists = [];

        $methodElm = '
/**
* @OA\Get(
*      path="/ignore{[RESOURCE]}",
*      summary="[RESOURCE]",
*      description="[RESOURCE] Error",
*      operationId="[RESOURCE]Error",
*      tags={"Exceptions"},
*      @OA\Response(
*          response=422,
*          description="[RESOURCE] Error",
*          @OA\JsonContent(
*             @OA\Property(type="object", property="errors", ref="#/components/schemas/ErrorsResponse/properties/[RESOURCE]")
*          )
*      )
*  )
*/
';

        $swaggerElm = '
/**
   @OA\Schema (
       [RESOURCE_LIST]
   )
 */';

        // Getting the path for language resources
        $basePath = base_path();


        // Getting Path
        if (strpos($basePath, '\\') !== false) {
            $path = $basePath . '\\resources\\lang\\ko';

            //write Path
            $errorWritePath = $basePath . '\\app\\Schemas\\ErrorsResponse.php';
        } else {
            $path = $basePath . '/resources/lang/ko';

            //write Path
            $errorWritePath = $basePath . '/app/Schemas/ErrorsResponse.php';
        }

        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file == '.' || $file == '..') continue;
                    $baseName = pathinfo($file, PATHINFO_FILENAME);
                    $lists[$baseName] = include($path . '/' . $file);
                }
                closedir($dh);
            }

            $res = [];
            foreach ($lists as $resource => $arr) {
                if ($resource == 'exceptions') {
                    $res = array_merge_recursive($res, $this->assignCodeByArray($arr, $resource));
                } else {
                    if (!isset($res[$resource])) {
                        $res[$resource] = [];
                    }
                    $res[$resource] = array_merge($res[$resource], $this->assignCodeByArray($arr, $resource));
                }
            }
        }

        $contents = "<?php\r\n namespace App\Schemas;\r\n";
        $schemas = [];

        foreach ($res as $resource => $arr) {
            $resourceMethodElm = str_replace('[RESOURCE]', $resource, $methodElm);
            $contents .= $resourceMethodElm . "\r\n";

            $propertyElm = '
@OA\Property(
   property="[RESOURCE]", type="array", collectionFormat="multi", example={[ERROR_LISTS]},
   @OA\Items(
       @OA\Property(property="code", type="string", description="Error Code"),
       @OA\Property(property="target", type="string", description="Error Code Target Attribute"),
       @OA\Property(property="msg", type="string", description="Error Code Message")
   ),
)';

            $resourceSchemaElm = str_replace('[ERROR_LISTS]', "\r\n" . implode(",\r\n", $arr), $propertyElm);
            $resourceSchemaElm = str_replace('[RESOURCE]', $resource, $resourceSchemaElm);
            $schemas[] = $resourceSchemaElm;
        }

        $contents .= str_replace('[RESOURCE_LIST]', implode(",\r\n", $schemas), $swaggerElm) . "\r\n";
        $contents .= "abstract class ErrorsResponse extends Model {}";

        file_put_contents($errorWritePath, $contents);
    }

    protected function assignCodeByArray($arr, $parent = '')
    {
        $parent = $parent ?? '';
        $customFlag = $parent == 'exceptions' ? true : false;


        $res = [];
        foreach ($arr as $k => $v) {
            $parent = $parent == 'exceptions' ? '' : $parent;
            $resourceKey = $parent ? $parent . '.' . $k : $k;
            if (is_array($v)) {
                $res = array_merge($res, $this->assignCodeByArray($v, $resourceKey));
            } else {
                $res[] = '{"code":"' . $resourceKey . '","msg":"' . $v . '"}';
            }
        }

        if($customFlag) {
            $_res = [];
            foreach ($res as $k => $v) {
                $jsonArr = json_decode($v);
                $resource = explode('.', $jsonArr->code)[0];
                $_res[$resource][] = $v;
            }
            return $_res;
        }

        return $res;
    }
}
