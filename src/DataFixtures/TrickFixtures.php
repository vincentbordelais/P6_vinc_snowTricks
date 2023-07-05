<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function getDependencies()
    {
        return [UserFixtures::class, CategoryFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {

        for ($i = 1; $i <= 10; $i++) {

            $trick = new Trick();
            // Choisir aléatoirement un auteur parmi les users :
            $author = $this->getReference('user_' . mt_rand(0, 4));

            // Récupérer aléatoirement la référence de la catégorie :
            $categoryReference = $this->getReference('category_' . mt_rand(1, 5));
            
            $trick->setName("Nom du trick n°".$i)
                ->setDescription("Description du trick n°$i")
                ->setCreatedDate(new \DateTime())
                ->setUpdatedDate(new \DateTime())
                ->setSlug($this->slugger->slug($trick->getName())->lower())
                ->setAuthor($author)
                ->addCategory($categoryReference);
            
            $manager->persist($trick);
        }

        $manager->flush();
    }
}
