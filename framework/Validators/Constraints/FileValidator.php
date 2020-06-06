<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 11:20 PM
 */

declare(strict_types=1);

namespace Rid\Validators\Constraints;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;

class FileValidator extends \Symfony\Component\Validator\Constraints\FileValidator
{
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);

        if ($value instanceof UploadedFile && $value->isValid()) {
            /** @var \Rid\Validators\Constraints\File $constraint */
            if ($constraint->extensions) {
                $extension = strtolower($value->getClientOriginalExtension());
                if (!in_array($extension, (array)$constraint->extensions, true)) {
                    $this->context->buildViolation($constraint->extensionMessage)
                        ->setParameter('{{ type }}', $this->formatValue($extension))
                        ->setParameter('{{ types }}', $this->formatValues($constraint->extensions))
                        ->setCode(File::INVALID_MIME_TYPE_ERROR)
                        ->addViolation();
                }
            }
        }
    }
}
