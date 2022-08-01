<?php

namespace App\Providers;

use App\Models\DicMain;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::listen(function ($query) {

        });

        Validator::extend('check_mrtcode', function ($attributes, $value, $parameters) {
            if (strlen($value) != 4) {
                return false;
            }

            $mrtcode = substr($value, 0, 2);
            $submrtcode = substr($value, 2, 2);
            $dic1 = DicMain::where('parentid', 3)->where('value', $mrtcode)->first();
            if (!$dic1) {
                return false;
            }

            $dic2 = DicMain::where('parentid', $dic1->id)->where('value', $submrtcode)->first();
            if (!$dic2) {
                return false;
            }

            return true;
        });

        Validator::extend('check_opercode', function ($attributes, $value, $parameters) {
            if (strlen($value) != 3) {
                return false;
            }

            $opercode1 = substr($value, 0, 1);
            $opercode2 = substr($value, 1, 2);
            $dic1 = DicMain::where('parentid', 1)->where('value', $opercode1)->first();
            if (!$dic1) {
                return false;
            }

            $dic2 = DicMain::where('parentid', $dic1->id)->where('value', $opercode2)->first();
            if (!$dic2) {
                return false;
            }

            return true;
        });

        Validator::extend('check_custregno', function ($attributes, $value, $parameters, $validator) {
            // Log::info($attributes);
            // Log::info($value);
            // Log::info($parameters);
            $inputs = $validator->getData();
            $is_foreign = "0";
            $is_organization = "0";
            $c = count($parameters);
            if ($c>1){
                $is_foreign = @$inputs[$parameters[0]];
                $is_organization = @$inputs[$parameters[1]];
            } else if ($c>0){
                $is_foreign = @$inputs[$parameters[0]];
            }
            if ($is_foreign == "1") {
                return true;
            } else {
                if ($is_organization == "1") {
                    return preg_match('/^[0-9]+$/', $value) ? true : false;
                } else {
                    return preg_match('/(*UTF8)^((?![ь,ъ,Ь,Ъ])[А-Яа-яӨөҮү]){2}[0-9]{8}$/', $value) ? true : false;
                }
            }
        });
        // Validator::replacer('check_custregno', function($message, $attribute, $rule, $parameters) {
        //     return "The $attribute field does not match with the format";
        // });        
    }

    public function register()
    {
        //
    }
}
