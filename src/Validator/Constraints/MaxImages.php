<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MaxImages extends Constraint
{
    public $message = 'Vous ne pouvez pas télécharger plus de {{ limit }} images.';
    public $maxImages;

    public function validatedBy()
    {
        return \get_class($this).'Validator'; // retourne le nom de la classe Validator qui sera utilisée pour valider la contrainte.


    }
}
