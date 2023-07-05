<?php

namespace App\DataFixtures;

use App\Entity\VideoYT;
use App\DataFixtures\TrickFixtures;
use App\Repository\TrickRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

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

    // Exemples :
    // https://www.youtube.com/watch?v=l69YDfVU9qs  // Old school
    // https://www.youtube.com/watch?v=MUB_YhSiK_o  // one foot
    // https://www.youtube.com/watch?v=nCFkNIaL7yM  // flip
    // https://www.youtube.com/watch?v=oI-umOzNBME  // Rotations désaxées
    // https://www.youtube.com/watch?v=oAK9mK7wWvw  // slides

    private $videoYTData = [
        ['slugCategory' => 'old-school', 'ytVideoId' => 'l69YDfVU9qs'],
        ['slugCategory' => 'one-foot', 'ytVideoId' => 'MUB_YhSiK_o'],
        ['slugCategory' => 'flip', 'ytVideoId' => 'nCFkNIaL7yM'],
        ['slugCategory' => 'rotations-desaxees', 'ytVideoId' => 'oI-umOzNBME'],
        ['slugCategory' => 'slides', 'ytVideoId' => 'oAK9mK7wWvw']
    ];

    public function load(ObjectManager $manager): void
    {
        $tricks = $this->trickRepository->findAll();

        foreach ($tricks as $trick) {
            // une ou pas de vidéos :
            $videoYTCount = mt_rand(0, 1);

            if($videoYTCount > 0){
                $videoYT = new VideoYT();
                $videoYT->setTrick($trick);

                $categories = $trick->getCategories();
                $categorySlug = $categories->first()->getSlug();
                $ytVideoId = $this->getYtVideoIdBySlug($categorySlug);
                $videoYT->setYtVideoId($ytVideoId);

                $manager->persist($videoYT);
            }
        }

        $manager->flush();
    }

    private function getYtVideoIdBySlug(string $slug): ?string
    {
        foreach ($this->videoYTData as $oneVideoYTData) {
            if ($oneVideoYTData['slugCategory'] === $slug) {
                return $oneVideoYTData['ytVideoId'];
            }
        }
    }
}
