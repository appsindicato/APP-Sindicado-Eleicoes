<?php

class SecretUrlPlugin
{
	public static function loadSecret($id,$key,$encrypt_key){
        if(!function_exists('hash_equals'))
        {
            function hash_equals($str1, $str2)
            {
                if(strlen($str1) != strlen($str2))
                {
                    return false;
                }
                else
                {
                    $res = $str1 ^ $str2;
                    $ret = 0;
                    for($i = strlen($res) - 1; $i >= 0; $i--)
                    {
                        $ret |= ord($res[$i]);
                    }
                    return !$ret;
                }
            }
        }

        if (hash_equals ( hash('sha256', $id .  $encrypt_key) , $key ) ) {
            return true;
        } else {
            return false;
        }

    }

    public static function saveSecret($id,$encrypt_key){
        return hash('sha256', $id . $encrypt_key);
    }	
}
