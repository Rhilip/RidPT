<?php

namespace Rid\Libraries;

/**
 * Captcha类
 */
class Captcha
{

    // 宽度
    protected int $width;

    // 高度
    protected int $height;

    // 字集合
    protected string $wordSet;

    // 字数
    protected int $wordNumber = 4;

    // 字体文件
    protected string $fontFile = '';

    // 字体大小
    protected int $fontSize = 20;

    // 字距
    protected float $xSpacing = 0.8;

    // 角度随机
    protected array $angleRand = [-20, 20];

    // Y轴随机
    protected array $yRand = [5, 15];

    // 文本
    protected ?string $_text = null;

    // 内容
    protected ?string $_content = null;

    // 生成
    public function generate()
    {
        $canvas = imagecreatetruecolor($this->width, $this->height);
        $background = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        $fontColor = imagecolorallocate($canvas, 32, 64, 160);
        imagefill($canvas, 0, 0, $background);
        $this->_text = '';
        for ($i = 1; $i <= $this->wordNumber; $i++) {
            $word = iconv_substr($this->wordSet, floor(mt_rand(0, mb_strlen($this->wordSet, 'utf-8') - 1)), 1, 'utf-8');
            $this->_text .= $word;
            imagettftext($canvas, $this->fontSize, mt_rand($this->angleRand[0], $this->angleRand[1]), $this->fontSize * ($this->xSpacing * $i), $this->fontSize + mt_rand($this->yRand[0], $this->yRand[1]), $fontColor, $this->fontFile, $word);
        }
        imagesavealpha($canvas, true);
        ob_start();
        imagepng($canvas);
        imagedestroy($canvas);
        $this->_content = ob_get_clean();
    }

    // 获取文本
    public function getText()
    {
        return $this->_text;
    }

    // 获取内容
    public function getContent()
    {
        return $this->_content;
    }
}
