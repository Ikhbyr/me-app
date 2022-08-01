<?php

namespace App\Services;

use App\Models\DicMain;
use Exception;
use Illuminate\Support\Facades\DB;

class DicService
{
    public function get()
    {
        $dic = DicMain::whereNotNull('maintype')->whereNull('parentid')->where('status', 1);
        return $dic;
    }

    public function getDic($typeName)
    {
        $dic = DicMain::where('maintype', $typeName)->where('status', 1)->first();
        if (!$dic) {
            throw new Exception("Dictionary [" . $typeName . "] not found!");
        }

        return $dic->children()->where('status', 1);
    }

    public function getDicWithChildren($typeName)
    {
        $dic = DicMain::where('maintype', $typeName)->where('status', 1)->first();
        if (!$dic) {
            throw new Exception("Dictionary [" . $typeName . "] not found!");
        }
        return $dic->children()->with(['children:parentid,name,value'])->get();
    }

    public function getDicByParent($parentid)
    {
        $dic = DicMain::where('id', $parentid)->where('status', 1)->first();
        if (!$dic) {
            throw new Exception("Dictionary [" . $parentid . "] not found!");
        }

        return $dic->children()->where('status', 1);
    }

    public function add($typeName, $value, $name, $info = "", $parentid = null)
    {
        $dic = DicMain::where('maintype', $typeName)->where('status', 1)->first();
        if ($dic) {
            throw new Exception("Dictionary [" . $typeName . "] already exists!");
        }
        $dic = new DicMain();
        $dic->name = $name;
        $dic->value = $value;
        $dic->maintype = $typeName;
        $dic->info = $info;
        $dic->status = 1;
        $dic->parentid = $parentid;
        $dic->save();
        return $dic;
    }

    public function delete($typeName)
    {
        $dic = DicMain::where('maintype', $typeName)->where('status', 1)->first();
        if (!$dic) {
            return getSystemResp("Dictionary [" . $typeName . "] not found!", 500);
        }

        if ($dic->sys == '1') {
            return getSystemResp('Хандах эрхгүй байна.', 500);
        }
        $dic->status = -1;
        $dic->save();
        DB::update('update dic_main set status = -1 where parentid = ' . $dic->id);
        DB::commit();
        return getSystemResp($dic);
    }

    public function restore($typeName)
    {
        $dic = DicMain::where('maintype', $typeName)->where('status', -1)->first();
        if (!$dic) {
            throw new Exception("Dictionary [" . $typeName . "] not deleted!");
        }
        $dic->status = 1;
        $dic->save();
        return $dic;
    }

    public function setItem($id, $value, $name, $parentid = null, $maintype = "")
    {
        // $dic = DicMain::where('id', $parentid)->where('status', 1)->first();
        // if (!$dic) {
        //     throw new Exception("Dictionary [".$parentid."] not found!");
        // }
        $child = DicMain::where('id', $id)->first();
        if (!$child) {
            return getSystemResp("Dictionary [" . $parentid . "] does not have child id: " . $id . "!", 500);
        }

        if ($child->sys == '1') {
            return getSystemResp('Хандах эрхгүй байна.', 500);
        }
        $child->value = $value;
        $child->name = $name;
        if ($parentid) {
            $child->parentid = $parentid;
        }
        if ($maintype) {
            $child->maintype = $maintype;
        }
        $child->save();
        return getSystemResp($child, 200);
    }

    public function addItem($parentid, $value, $name, $maintype = "")
    {
        $dic = DicMain::where('id', $parentid)->where('status', 1)->first();
        if (!$dic) {
            throw new Exception("Dictionary [" . $parentid . "] not found!");
        }
        $child = new DicMain();
        $child->parentid = $parentid;
        $child->value = $value;
        $child->name = $name;
        $child->maintype = $maintype;
        $child->save();
        return $child;
    }

    public function deleteItem($id)
    {
        $child = DicMain::where('id', $id)->first();
        if (!$child) {
            return getSystemResp("Dictionary [" . $id . "] child not found!", 500);
        }
        if ($child->sys == '1') {
            return getSystemResp('Хандах эрхгүй байна.', 500);
        }
        $child->status = -1;
        $child->save();
        return getSystemResp($child);
    }

    public function restoreItem($id)
    {
        $child = DicMain::where('id', $id)->first();
        if (!$child) {
            throw new Exception("Dictionary [" . $id . "] child not found!");
        }

        $child->status = 1;
        $child->save();
        return $child;
    }
}
