<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    private $userRepository;
    private $trickRepository;

    public function __construct(UserRepository $userRepository, TrickRepository $trickRepository)
    {
        $this->userRepository = $userRepository;
        $this->trickRepository = $trickRepository;
    }
    
    public function getDependencies()
    {
        return [TrickFixtures::class];
    }
    
    public function load(ObjectManager $manager): void
    {
        $firstTrick = $this->trickRepository->findOneBy([]);

        // 35 commentaires sur le premier trick écris par un user choisi aléatoirement :
        if ($firstTrick) {
            $users = $this->userRepository->findAll();

            for ($i = 1; $i <= 35; $i++) {
                $comment = new Comment();
                $comment->setComment("Commentaire n°$i")
                    ->setCreatedDate(new \DateTime())
                    ->setTrick($firstTrick)
                    ->setUser($users[array_rand($users)]);

                $manager->persist($comment);
            }
        }
        
        $tricks = $this->trickRepository->findAll();
        $users = $this->userRepository->findAll();

        foreach ($tricks as $trick) {
            // 0 à 10 commentaires aléatoires sur les autres tricks :
            if ($trick !== $firstTrick) {
                $commentCount = mt_rand(0, 10);
    
                for ($i = 1; $i <= $commentCount; $i++) {
                    $comment = new Comment();
                    $comment->setComment("Commentaire n°$i")
                        ->setCreatedDate(new \DateTime())
                        ->setTrick($trick)
                        ->setUser($users[array_rand($users)]);
    
                    $manager->persist($comment);
                }
            }
        }
        $manager->flush();
    }
}
