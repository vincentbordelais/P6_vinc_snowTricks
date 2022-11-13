<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $trick = new Trick();
            $trick->setName("Nom du trick n° $i")
                ->setDescription("<p>Description du trick n°$i</p>")
                ->setCreatedDate(new \DateTime()) // fait parti du namespace global de php
                ->setUpdatedDate(new \DateTime());
            $manager->persist($trick);
        }
        $manager->flush();
    }
}
