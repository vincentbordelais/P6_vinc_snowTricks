<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom',
                'empty_data' => ''
            ])
            ->add('description', null, [
                'label' => 'Description',
                'empty_data' => ''
            ])
            ->add(
                'categories',
                EntityType::class,
                [
                    'class' => Category::class,
                    'label' => 'Catégories',
                    'choice_label' => 'name',
                    'expanded' => true,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                    },
                    'by_reference' => false // Pour que le choix des catégories soit enregistré en base (en même temps que le trick via le flush dans notre TrickController), il faudrait que Symfony puisse faire $trick->setCategory() or dans l'entité Trick qui est en ManyToMany avec category il n'y a pas de setCategory(). Par contre il y a un addCategory(). Le paramètre by_reference avec la valeur false indique à Doctrine que la relation entre l'entité parente et l'entité enfant doit être gérée en utilisant une collection de type PersistentCollection. Cela signifie que les changements apportés aux objets enfants de la collection seront automatiquement suivis par Doctrine et seront persistés en base de données. En fait il clone l'objet, ce qui oblige le framework à appeler le setter sur l'objet parent.
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
