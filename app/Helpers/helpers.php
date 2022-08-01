<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('arrayToArray')) {
    function arrayToArray($array)
    {
        $results = [];
        $iterator = new RecursiveArrayIterator($array);
        iterator_apply($iterator, 'traverseStructure', array($iterator, &$results));
        return $results;
    }
}

if (!function_exists('traverseStructure')) {
    function traverseStructure($iterator, &$results, $parent = "")
    {
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                traverseStructure($iterator->getChildren(), $results, $parent . $iterator->key() . ".");
            } else {
                $results[$parent . $iterator->key()] = $iterator->current();
            }
            $iterator->next();
        }
    }
}

if (!function_exists('parse_csv')) {
    function parse_csv($filename_or_text, $delimiter = ',', $enclosure = '"', $linebreak = "\n")
    {
        $return = array();

        if (false !== ($csv = (filter_var($filename_or_text, FILTER_VALIDATE_URL) ? file_get_contents($filename_or_text) : $filename_or_text))) {
            $csv = trim($csv);
            $csv = mb_convert_encoding($csv, 'UTF-8');

            foreach (str_getcsv($csv, $linebreak, $enclosure) as $row) {
                $col = str_getcsv($row, $delimiter, $enclosure);
                $col = array_map('trim', $col);
                $return[] = $col;
            }
        } else {
            throw new \Exception('Can not open the file.');
            $return = false;
        }

        return $return;
    }
}

if (!function_exists('remove_utf8_bom')) {
    function remove_utf8_bom($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }
}

if (!function_exists('me_hmac')) {
    function me_hmac($text)
    {
        return hash_hmac('sha256', $text, "MeFiba=IS");
    }
}

if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('getOwnInstId')) {
    function getOwnInstId()
    {
        return env('OWN_INST_ID') . "";
    }
}

if (!function_exists('formatDate')) {
    function formatDate($strdt, $istime = true)
    {
        if (empty($strdt)) {
            return $strdt;
        }
        try {
            $date = strtotime($strdt);
            if ($istime) {
                return date('Y-m-d H:i:s', $date);
            } else {
                return date('Y-m-d', $date);
            }
        } catch (Exception $ex) {
            return $strdt;
        }
    }
}

if (!function_exists('getSystemResp')) {
    /**
     * Систем дотор ашиглагдах хүсэлтийн хариу
     *
     * @param  mixed $desc
     * @param  mixed $status
     * @return void
     */
    function getSystemResp($desc, $status = 200)
    {
        return [
            'data' => $desc,
            'status' => $status
        ];
    }
}

if (!function_exists('random_number')) {
    function random_number()
    {
        $rand = rand(10, 99);
        return date('YmdHis') . $rand;
    }
}

if (!function_exists('checkInstPerm')) {
    function checkInstPerm($permId, $instid, $user = null)
    {
        if (empty($user)) {
            $user = auth()->user();
        }
        if (!$user) {
            return false;
        }

        $sql = "SELECT *
                    FROM cust_role_perms
                WHERE     roleid IN
                                (SELECT roleid
                                    FROM cust_user_role
                                WHERE userid = :userid AND statusid = 1 AND instid = :instid)
                        AND instid = :instid
                        AND permid = :permid";
        $res = DB::select(DB::raw($sql), ['instid' => $instid, 'userid' => $user->userid, 'permid' => $permId]);
        if (empty($res)) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists('xmlToJson')) {
    function xmlToJson($data)
    {
        // Load xml data into xml data object
        $simpleXml = simplexml_load_string($data);
        // using json_encoe function
        $json = json_encode($simpleXml);
        $array = json_decode($json, TRUE);
        return $array;
    }
}

if (!function_exists('safeEncrypt')) {
    /**
     * Encrypt a message
     *
     * @param string $input - message to encrypt
     * @return string
     */
    function safeEncrypt($input)
    {
        $iv = "1234567890123456";
        $key = "MeFiba=ISO";
        return openssl_encrypt($input, "AES-128-CBC", $key, 0, $iv);
    }
}

if (!function_exists('safeDecrypt')) {
    /**
     * Decrypt a message
     *
     * @param string $encrypted - message encrypted with safeEncrypt()
     * @return string
     */
    function safeDecrypt($encrypted)
    {
        $key = "MeFiba=ISO";
        $iv = "1234567890123456";
        return openssl_decrypt($encrypted, "AES-128-CBC", $key, 0, $iv);
    }
}
