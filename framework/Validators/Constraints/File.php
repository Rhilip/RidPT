<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 11:19 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

class File extends \Symfony\Component\Validator\Constraints\File
{
    const INVALID_EXTENSION_ERROR = '8aaf0b09-636f-4e0b-8d1c-4322e6cd989d';

    protected static $errorNames = [
        self::NOT_FOUND_ERROR => 'NOT_FOUND_ERROR',
        self::NOT_READABLE_ERROR => 'NOT_READABLE_ERROR',
        self::EMPTY_ERROR => 'EMPTY_ERROR',
        self::TOO_LARGE_ERROR => 'TOO_LARGE_ERROR',
        self::INVALID_MIME_TYPE_ERROR => 'INVALID_MIME_TYPE_ERROR',
        self::INVALID_EXTENSION_ERROR => 'INVALID_EXTENSION_ERROR'
    ];

    public $extensions = null;
    public string $extensionMessage = 'The extension of the file is invalid ({{ type }}). Allowed extension are {{ types }}.';
}
