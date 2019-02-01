<?php

namespace Rid\Validators;

use Rid\Base\BaseObject;

use Rid\Http\UploadFile;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Docs: https://symfony.com/doc/current/reference/constraints.html
 *
 * Class Validator
 * @package Rid\Validators
 */
class Validator extends BaseObject
{
    const KIB_BYTES = 1024;
    const MIB_BYTES = 1048576;
    private static $suffices = array(
        1 => 'bytes',
        self::KIB_BYTES => 'KiB',
        self::MIB_BYTES => 'MiB',
    );

    public $captcha;

    /**  @var \Symfony\Component\Validator\ConstraintViolationListInterface */
    private $_errors;

    public static function rules()
    {
        return [];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $rules = self::rules();
        foreach ($rules as $property => $constraints) {
            $metadata->addPropertyConstraints($property, $constraints);
        }
    }

    public function importAttributes($config)
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
    }

    public function importFileAttributes($config)
    {
        foreach ($config as $name => $value) {
            $this->$name = UploadFile::newInstanceByName($name);
        }
    }

    public function validate()
    {
        $validator = Validation::createValidatorBuilder()
            ->addMethodMapping('loadValidatorMetadata')
            ->getValidator();
        $this->_errors = $validator->validate($this);
        return $this->_errors;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getError()
    {
        $errors = $this->_errors;
        if (empty($errors)) {
            return '';
        }

        return $errors->get(0);
    }

    public function validateCaptcha(ExecutionContextInterface $context, $payload)
    {
        $captchaText = app()->session->get('captchaText');
        if (strcasecmp($this->captcha, $captchaText) != 0) {
            $context->buildViolation("CAPTCHA verification failed")->addViolation();
        }
    }

    public function validateFile(ExecutionContextInterface $context, $payload)
    {
        $name = $payload['name'] ?? 'file';

        /**  @var \mix\Http\UploadFile $file */
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
            if ($file->getSize() > $limitInBytes) {
                list($sizeAsString, $limitAsString, $suffix) = $this->factorizeSizes($file->getSize(), $limitInBytes);
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
        $coef = self::MIB_BYTES;
        $coefFactor = self::KIB_BYTES;
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
        return array($sizeAsString, $limitAsString, self::$suffices[$coef]);
    }
}
