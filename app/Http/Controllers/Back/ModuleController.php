<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\MobileModuleList;
use App\Models\Modules;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function index(Request $request)
    {
        return Modules::where('status', '1')->orderBy('corder')->get();
    }

    public function getPerms(Request $request)
    {
        $module = Modules::where('status', '1')->where('moduleid', $request->moduleid)->first();
        if (!$module) return response()->json("Module not found!", 404);
        return $module->perms;
    }

    public function getMobileModules(Request $request)
    {
        return MobileModuleList::where('status', '1')->orderBy('corder')->get();
    }

    public function getMobilePerms(Request $request)
    {
        $module = MobileModuleList::where('status', '1')->where('moduleid', $request->moduleid)->first();
        if (!$module) return response()->json("Module not found!", 404);
        return $module->perms;
    }
}
