<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MaxImagesValidator extends ConstraintValidator
{
    
    public function validate($value, Constraint $constraint)
    {
        $limit = $constraint->maxImages;

        if (count($value) > $limit) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ limit }}', $limit)
                ->addViolation();
        }
    }
}
