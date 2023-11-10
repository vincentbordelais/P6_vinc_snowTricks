<?php

namespace App\DataFixtures;

use App\Entity\Video;
use App\Repository\TrickRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VideoYTFixtures extends Fixture implements DependentFixtureInterface
{
    private $trickRepository;

    public function __construct(TrickRepository $trickRepository)
    {
        $this->trickRepository = $trickRepository;
    }
    
    public function getDependencies()
    {
        return [TrickFixtures::class];
    }

    private $videoByCategory = [
        ['slugCategory' => 'old-school', 'videoUrl' => 'https://www.youtube.com/watch?v=l69YDfVU9qs'],
        ['slugCategory' => 'old-school', 'videoUrl' => 'https://www.youtube.com/watch?v=6pCl5CeWuII'],
        ['slugCategory' => 'old-school', 'videoUrl' => 'https://www.youtube.com/watch?v=dopMuNI74r0'],
        ['slugCategory' => 'old-school', 'videoUrl' => 'https://www.youtube.com/watch?v=b2MFR0rH6x4'],
        ['slugCategory' => 'old-school', 'videoUrl' => 'https://www.youtube.com/watch?v=V9xuy-rVj9w'],

        ['slugCategory' => 'one-foot', 'videoUrl' => 'https://www.youtube.com/watch?v=MUB_YhSiK_o'],
        ['slugCategory' => 'one-foot', 'videoUrl' => 'https://www.youtube.com/watch?v=p0_GXFmyZnc'],
        ['slugCategory' => 'one-foot', 'videoUrl' => 'https://www.youtube.com/watch?v=jOn7VQ89rig'],
        ['slugCategory' => 'one-foot', 'videoUrl' => 'https://www.youtube.com/watch?v=3GHU3DN1v4Q'],
        ['slugCategory' => 'one-foot', 'videoUrl' => 'https://www.youtube.com/watch?v=d7dpo_G9npo'],

        ['slugCategory' => 'flip', 'videoUrl' => 'https://www.youtube.com/watch?v=nCFkNIaL7yM'],
        ['slugCategory' => 'flip', 'videoUrl' => 'https://www.youtube.com/watch?v=arzLq-47QFA'],
        ['slugCategory' => 'flip', 'videoUrl' => 'https://www.youtube.com/watch?v=vf9Z05XY79A'],
        ['slugCategory' => 'flip', 'videoUrl' => 'https://www.youtube.com/watch?v=vIqaebj-GNw'],
        ['slugCategory' => 'flip', 'videoUrl' => 'https://www.youtube.com/watch?v=bfgCc_Bp8Ow'],

        ['slugCategory' => 'rotations-desaxees', 'videoUrl' => 'https://www.youtube.com/watch?v=k2W3g5C2Y1o'],
        ['slugCategory' => 'rotations-desaxees', 'videoUrl' => 'https://www.youtube.com/watch?v=Naa0wbyZEPo'],
        ['slugCategory' => 'rotations-desaxees', 'videoUrl' => 'https://www.youtube.com/watch?v=a8AoVK0ppT8'],
        ['slugCategory' => 'rotations-desaxees', 'videoUrl' => 'https://www.youtube.com/watch?v=WHIxDwbR7Io'],
        ['slugCategory' => 'rotations-desaxees', 'videoUrl' => 'https://www.youtube.com/watch?v=oI-umOzNBME'],

        ['slugCategory' => 'slides', 'videoUrl' => 'https://www.youtube.com/watch?v=oAK9mK7wWvw'],
        ['slugCategory' => 'slides', 'videoUrl' => 'https://www.youtube.com/watch?v=xczPvfa2LIk'],
        ['slugCategory' => 'slides', 'videoUrl' => 'https://www.youtube.com/watch?v=gRZCF5_XRsA'],
        ['slugCategory' => 'slides', 'videoUrl' => 'https://www.youtube.com/watch?v=Dafmcn0UR5g'],
        ['slugCategory' => 'slides', 'videoUrl' => 'https://www.youtube.com/watch?v=YM6ElecYTDM']
    ];

    public function load(ObjectManager $manager): void
    {
        $tricks = $this->trickRepository->findAll();
    
        foreach ($tricks as $trick) {
            $videoCount = mt_rand(1, 5); // Générer un nombre aléatoire entre 1 et 5
    
            for ($i = 1; $i <= $videoCount; $i++) {
                $video = new Video();
                $video->setTrick($trick);

                $categories = $trick->getCategories();
                $category = $categories->first()->getSlug(); // première catégorie associée au trick en cours.

                $videoUrl = $this->getRandomVideoUrlByCategory($category); // retourne une URL aléatoire
                $video->setUrl($videoUrl);
    
                $manager->persist($video);
            }
        }
        $manager->flush();
    }

    private function getRandomVideoUrlByCategory(string $slug): ?string
    {
        // Parcourir notre tableau et obtenir toutes les URLs pour une catégorie
        $videosUrl = [];
        foreach ($this->videoByCategory as $oneVideoByCategory) {
            if ($oneVideoByCategory['slugCategory'] === $slug) {
                $videosUrl[] = $oneVideoByCategory['videoUrl'];
            }
        }

        // Mélanger l'ordre des URLs et retourner la première
        shuffle($videosUrl);
        return $videosUrl[0] ?? null;
    }
}
