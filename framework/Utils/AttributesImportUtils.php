<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/27
 * Time: 12:47
 */

namespace Mix\Utils;


trait AttributesImportUtils
{
    public function importAttributes($config)
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
    }
}
