<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/22
 * Time: 10:57
 */

namespace apps\common\traits;

use SandFoxMe\Bencode\Exceptions\ParseErrorException;

trait TorrentTraits
{
    private function checkTorrentDict($dict, $key, $type = null) {
        if (!is_array($dict))
            throw new ParseErrorException("std_not_a_dictionary");

        $value = $dict[$key];
        if (!$value) {
            throw new ParseErrorException("std_dictionary_is_missing_key");
        }

        if (!is_null($type)) {
            $isFunction = 'is_'.$type;
            if (\function_exists($isFunction) && !$isFunction($value)) {
                throw new ParseErrorException("std_invalid_entry_in_dictionary");
            }
        }

        return $value;
    }
}
