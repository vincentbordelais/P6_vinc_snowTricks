<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture
{
    private $categoryData = [
        [
            'name' => 'Old school',
            'description' => 'Le terme old school désigne un style de freestyle caractérisée par en ensemble de figure et une manière de réaliser des figures passée de mode, qui fait penser au freestyle des années 1980 - début 1990 (par opposition à new school) :
    
            figures désuètes : Japan air, rocket air, ...
            rotations effectuées avec le corps tendu
            figures saccadées, par opposition au style new school qui privilégie l\'amplitude
    
            À noter que certains tricks old school restent indémodables :
    
            Backside Air
            Method Air'
        ],
        [
            'name' => 'One foot',
            'description' => 'Figures réalisée avec un pied décroché de la fixation, afin de tendre la jambe correspondante pour mettre en évidence le fait que le pied n\'est pas fixé. Ce type de figure est extrêmement dangereuse pour les ligaments du genou en cas de mauvaise réception. '
        ],
        [
            'name' => 'Flip',
            'description' => 'Un flip est une rotation verticale. On distingue les front flips, rotations en avant, et les back flips, rotations en arrière.
            Il est possible de faire plusieurs flips à la suite, et d\'ajouter un grab à la rotation.
            Les flips agrémentés d\'une vrille existent aussi (Mac Twist, Hakon Flip, ...), mais de manière beaucoup plus rare, et se confondent souvent avec certaines rotations horizontales désaxées.
            Néanmoins, en dépit de la difficulté technique relative d\'une telle figure, le danger de retomber sur la tête ou la nuque est réel et conduit certaines stations de ski à interdire de telles figures dans ses snowparks.'
        ],
        [
            'name' => 'Rotations désaxées',
            'description' => 'Une rotation désaxée est une rotation initialement horizontale mais lancée avec un mouvement des épaules particulier qui désaxe la rotation. Il existe différents types de rotations désaxées (corkscrew ou cork, rodeo, misty, etc.) en fonction de la manière dont est lancé le buste. Certaines de ces rotations, bien qu\'initialement horizontales, font passer la tête en bas.
    
            Bien que certaines de ces rotations soient plus faciles à faire sur un certain nombre de tours (ou de demi-tours) que d\'autres, il est en théorie possible de d\'attérir n\'importe quelle rotation désaxée avec n\'importe quel nombre de tours, en jouant sur la quantité de désaxage afin de se retrouver à la position verticale au moment voulu.
            
            Il est également possible d\'agrémenter une rotation désaxée par un grab.'
        ],
        [
            'name' => 'Slides',
            'description' => 'Un slide consiste à glisser sur une barre de slide. Le slide se fait soit avec la planche dans l\'axe de la barre, soit perpendiculaire, soit plus ou moins désaxé.

            On peut slider avec la planche centrée par rapport à la barre (celle-ci se situe approximativement au-dessous des pieds du rideur), mais aussi en nose slide, c\'est-à-dire l\'avant de la planche sur la barre, ou en tail slide, l\'arrière de la planche sur la barre.'
        ]
    ];

    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function getDependencies()
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $count = count($this->categoryData);
    
        for ($i = 0; $i < $count; $i++) {
            $category = new Category();
            $category->setName($this->categoryData[$i]['name'])
                ->setSlug($this->slugger->slug($this->categoryData[$i]['name'])->lower())
                ->setDescription($this->categoryData[$i]['description']);
    
            $manager->persist($category);
    
            // Ajouter une référence à la catégorie avec une clé unique
            $this->addReference('category_' . ($i + 1), $category);
        }
    
        $manager->flush();
    }
}
