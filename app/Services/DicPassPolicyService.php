<?php

namespace App\Services;

use App\Models\DicPassPolicy;
use Exception;
use Illuminate\Support\Facades\Log;

class DicPassPolicyService
{
    public function get()
    {
        return DicPassPolicy::get();
    }

    /**
     * Encrypt a message
     *
     * @param string $input - message to encrypt
     * @return string
     */
    public function safeEncrypt($input)
    {
        $iv = "1234567890123456";
        $key = "MeFiba=IS";
        return openssl_encrypt($input, "AES-128-CBC", $key, 0, $iv);
    }

    /**
     * Decrypt a message
     *
     * @param string $encrypted - message encrypted with safeEncrypt()
     * @return string
     */
    public function safeDecrypt($encrypted)
    {
        $key = "MeFiba=IS";
        $iv = "1234567890123456";
        return openssl_decrypt($encrypted, "AES-128-CBC", $key, 0, $iv);
    }

    /**
     * Hash HMAC a message
     *
     * @param string $text - Гараас оруулах утга
     * @return string
     */
    public function hash_hmac($text)
    {
        $key = "MeFiba=IS";
        return hash_hmac('sha256', $text, $key);
    }

    public function getPolicyValue($optionName)
    {
        $dic = DicPassPolicy::where('optionname', $optionName)->first();
        if (!$dic) {
            return throw new Exception("Pass policy [" . $optionName . "] not found!");
        }

        return $dic->optionvalue;
    }

    // Нууц үгийн полиси шалгах
    public function checkPassPolicy($password)
    {
        // Их урт шалгах
        $highValue = $this->getPolicyValue("PassHighLength");
        if (strlen($password) > $highValue) {
            return throw new Exception("Password maximum length " . $highValue);
        } else {
            // Бага урт шалгах
            $minLength = $this->getPolicyValue("PassLowLength");
            if (strlen($password) < $minLength) {
                return throw new Exception("Password minimum length " . $minLength);
            } else {
                // Том үсэг шалгах
                $valid = $this->getPolicyValue("MustUpperLetter");
                if ($valid === '1' && !$this->checkLeastOneLetter($password, "UpperLetter")) {
                    return throw new Exception("Least one uppercase letter");
                } else {
                    // Жижиг үсэг шалгах
                    $valid = $this->getPolicyValue("MustLowerLetter");
                    if ($valid === '1' && !$this->checkLeastOneLetter($password, "LowerLetter")) {
                        return throw new Exception("Least one lowercase letter");
                    } else {
                        // Тусгай тэмдэгт шалгах
                        $valid = $this->getPolicyValue("MustPunctuation");
                        if ($valid === '1' && !$this->checkLeastOneLetter($password, "Punctuation")) {
                            return throw new Exception("Тусгай тэмдэгт оруулна уу. /" . $this->getPolicyValue("Punctuation") . "/");
                        } else {
                            // Тоо тэмдэгт шалгах
                            $valid = $this->getPolicyValue("MustNumber");
                            if ($valid === '1' && !$this->checkLeastOneLetter($password, "Numbers")) {
                                return throw new Exception("Least one number letter");
                            } else {
                                // Шалгалтуудыг амжилттай давлаа. ;)
                            }
                        }
                    }
                }
            }
        }
    }

    // text дотор optionName байгаа эсэх
    public function checkLeastOneLetter($text, $optionName)
    {
        $valid = $this->getPolicyValue($optionName);
        return preg_match("/[" . $valid . "]/", $text);
    }

    public function random($length = 16)
    {
        $random_string = "";
        $valid_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

        $num_valid_chars = strlen($valid_chars);

        for ($i = 0; $i < $length; $i++) {
            $random_pick = mt_rand(1, $num_valid_chars);
            $random_char = $valid_chars[$random_pick - 1];
            $random_string .= $random_char;
        }
        return $random_string;
    }
}
