<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/8
 * Time: 18:52
 */

namespace Rid\Validators;

use Rid\Http\UploadFile;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait FileTrait
{

    public function importFileAttributes($config)
    {
        foreach ($config as $name => $value) {
            $this->$name = UploadFile::newInstanceByName($name);
        }
    }

    public function validateFile(ExecutionContextInterface $context, $payload)
    {
        $name = $payload['name'] ?? 'file';

        /**  @var \Rid\Http\UploadFile $file */
        $file = $this->$name;

        if ($file->error > 0) {
            switch ($file->error) {
                case UPLOAD_ERR_INI_SIZE:
                    $context->buildViolation("The file is too large.")
                        ->setCode(UPLOAD_ERR_INI_SIZE)
                        ->addViolation();
                    return;
                case UPLOAD_ERR_FORM_SIZE:
                    $context->buildViolation("The file is too large.")
                        ->setCode(UPLOAD_ERR_FORM_SIZE)
                        ->addViolation();
                    return;
                case UPLOAD_ERR_PARTIAL:
                    $context->buildViolation("The file was only partially uploaded.")
                        ->setCode(UPLOAD_ERR_PARTIAL)
                        ->addViolation();
                    return;
                case UPLOAD_ERR_NO_FILE:
                    $context->buildViolation("No file was uploaded.")
                        ->setCode(UPLOAD_ERR_NO_FILE)
                        ->addViolation();
                    return;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $context->buildViolation("No temporary folder was configured in php.ini.")
                        ->setCode(UPLOAD_ERR_NO_TMP_DIR)
                        ->addViolation();
                    return;
                case UPLOAD_ERR_CANT_WRITE:
                    $context->buildViolation("Cannot write temporary file to disk.")
                        ->setCode(UPLOAD_ERR_CANT_WRITE)
                        ->addViolation();
                    return;
                case UPLOAD_ERR_EXTENSION:
                    $context->buildViolation("A PHP extension caused the upload to fail.")
                        ->setCode(UPLOAD_ERR_EXTENSION)
                        ->addViolation();
                    return;
                default:
                    $context->buildViolation("The file could not be uploaded.")
                        ->setCode($file->error)
                        ->addViolation();
                    return;
            }
        }
        if (isset($payload['maxSize'])) {
            $limitInBytes = $payload['maxSize'];
            if ($file->size > $limitInBytes) {
                list($sizeAsString, $limitAsString, $suffix) = $this->factorizeSizes($file->size, $limitInBytes);
                $context->buildViolation("The file {{ name }} is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.")
                    ->setParameter('{{ name }}', $file->name)
                    ->setParameter('{{ size }}', $sizeAsString)
                    ->setParameter('{{ limit }}', $limitAsString)
                    ->setParameter('{{ suffix }}', $suffix)
                    ->setCode(File::TOO_LARGE_ERROR)
                    ->addViolation();
                return;
            }
        }
        if (isset($payload['mimeTypes'])) {
            $mime = $file->type;
            $mimeTypes = (array) $payload['mimeTypes'];
            foreach ($mimeTypes as $mimeType) {
                if ($mimeType === $mime) {
                    return;
                }
                if ($discrete = strstr($mimeType, '/*', true)) {
                    if (strstr($mime, '/', true) === $discrete) {
                        return;
                    }
                }
            }
            $context->buildViolation("The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}")
                ->setParameter('{{ name }}', $file->name)
                ->setParameter('{{ type }}', $mime)
                ->setParameter('{{ types }}', implode(',', $mimeTypes))
                ->setCode(File::INVALID_MIME_TYPE_ERROR)
                ->addViolation();
        }
    }

    private static function moreDecimalsThan($double, $numberOfDecimals)
    {
        return \strlen((string)$double) > \strlen(round($double, $numberOfDecimals));
    }

    /**
     * Convert the limit to the smallest possible number
     * (i.e. try "MB", then "kB", then "bytes").
     * @param $size
     * @param $limit
     * @return array
     */
    private function factorizeSizes($size, $limit)
    {
        $coef = 1048576;  // MIB_BYTES
        $coefFactor = 1024;  // KIB_BYTES
        $suffices = array( 1 => 'bytes', 1024 => 'KiB', 1048576 => 'MiB', );

        $limitAsString = (string)($limit / $coef);
        // Restrict the limit to 2 decimals (without rounding! we
        // need the precise value)
        while (self::moreDecimalsThan($limitAsString, 2)) {
            $coef /= $coefFactor;
            $limitAsString = (string)($limit / $coef);
        }
        // Convert size to the same measure, but round to 2 decimals
        $sizeAsString = (string)round($size / $coef, 2);
        // If the size and limit produce the same string output
        // (due to rounding), reduce the coefficient
        while ($sizeAsString === $limitAsString) {
            $coef /= $coefFactor;
            $limitAsString = (string)($limit / $coef);
            $sizeAsString = (string)round($size / $coef, 2);
        }
        return array($sizeAsString, $limitAsString, $suffices[$coef]);
    }
}
