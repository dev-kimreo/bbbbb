<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Closure;
use Cache;
use Storage;
use Artisan;


//use App\Models\Post;
//use App\Http\Requests\Posts\GetListPostsRequest;

use App\Libraries\PageCls;

/**
 * Class AttachController
 * @package App\Http\Controllers
 */
class AttachController extends Controller
{

    public function create(Request $request) {
//        Storage::disk('temp')->put('file.txt', 'Contents');

        echo Storage::disk('temp')->url('자연환경02.png');



//        Artisan::call('tempAttach:delete');
//        Storage::disk('temp')->putFileAs('', $request->file('image'), $request->file('image')->getClientOriginalName());

//        $request->file('image')->getClientOriginalName()
//        print_r($request->file('image')->getClientMimeType());
//        print_r($request->file('image')->extension());

    }




}
