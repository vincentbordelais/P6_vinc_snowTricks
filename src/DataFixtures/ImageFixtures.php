<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Repository\TrickRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    public const SOURCE_DIR_IMAGEFIXTURES = __DIR__ . '/../../public/forDataFixtures/images/';
    public const DEST_DIR_IMAGEFIXTURES = __DIR__ . '/../../public/uploads/images/';

    private $trickRepository;

    public function __construct(TrickRepository $trickRepository)
    {
        $this->trickRepository = $trickRepository;
    }

    public function getDependencies()
    {
        return [TrickFixtures::class];
    }
    
    public function load(ObjectManager $manager): void
    {
        $tricks = $this->trickRepository->findAll();

        foreach ($tricks as $trick) {
            $imageCount = mt_rand(1, 3);

            for ($i = 1; $i <= $imageCount; $i++) {
                // Choisir dans le directory des images source aléatoirement une image :
                $sourceImageFile = $this->getRandomImageFile(self::SOURCE_DIR_IMAGEFIXTURES);

                // Génèrer un nom de fichier unique :
                $destinationImageFile = md5(uniqid()) . '.webp';

                // Chemin complet de l'image source :
                $sourceImagePath = self::SOURCE_DIR_IMAGEFIXTURES . $sourceImageFile;
                // Chemin complet de l'image de destination :
                $destinationImagePath = self::DEST_DIR_IMAGEFIXTURES . $destinationImageFile;

                // Copier physiquement le fichier source vers le dossier d'images :
                copy($sourceImagePath, $destinationImagePath);

                $image = new Image();
                $image->setName($destinationImageFile);
                $image->setTrick($trick);

                $manager->persist($image);
            }
        }

        $manager->flush();
    }

    private function getRandomImageFile(string $directory): string
    {
        $files = scandir($directory); // tableau avec les noms des fichiers du répertoire

        // Supprimer les fichiers spéciaux (.DS_Store, ..) du tableau des fichiers :
        $files = array_filter($files, function ($file) {
            return !in_array($file, ['.', '..', '.DS_Store']);
        });

        return $files[array_rand($files)];
    }
}
