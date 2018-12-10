<?php

namespace mix\console;

use mix\base\Component;
use mix\helpers\PhpInfoHelper;

/**
 * Output组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Output extends Component
{

    // 颜色
    const NONE = "\033[0m";
    const BOLD = "\033[1m";

    const FG_BLACK = "\033[30m";
    const FG_RED = "\033[31m";
    const FG_GREEN = "\033[32m";
    const FG_YELLOW = "\033[33m";
    const FG_BLUE = "\033[34m";

    const BG_BLACK = "\033[30m";
    const BG_RED = "\033[41m";
    const BG_GREEN = "\033[42m";
    const BG_YELLOW = "\033[43m";
    const BG_BLUE = "\033[44m";

    // 是否为 WIN 操作系统
    protected $_isWin;

    // 构造事件
    public function onConstruct()
    {
        $this->_isWin = PhpInfoHelper::isWin();
    }

    // ANSI 格式化
    public function ansiFormat($message, $color = self::NONE)
    {
        if ($this->_isWin) {
            return $message;
        }
        return $color . $message . self::NONE;
    }

    // 写入
    public function write($message, $color = self::NONE)
    {
        echo $this->ansiFormat($message, $color);
    }

    // 写入，带换行
    public function writeln($message, $color = self::NONE)
    {
        echo $this->ansiFormat($message, $color) . PHP_EOL;
    }

}
