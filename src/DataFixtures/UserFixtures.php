<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixtures extends Fixture
{
    private $nameData = ['Vincent Bordelais', 'Laetitia Molle', 'Jérôme Gasti', 'Matthieu Davet', 'Patrick Lujan'];
    private $passwordData = 'aze';
    private $avatarBaseUrl = "https://api.dicebear.com/6.x/adventurer-neutral/svg?seed=";

    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function getDependencies()
    {
        return [];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < count($this->nameData); $i++) {
            $user = new User();
            $nameParts = explode(' ', $this->nameData[$i]);

            $user->setEmail($this->generateEmailFromName($this->nameData[$i]));
            if ($this->nameData[$i] === 'Vincent Bordelais') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            };

            $user->setPassword($this->userPasswordHasher->hashPassword($user, $this->passwordData))
                ->setUsername($this->generateUsernameFromName($this->nameData[$i]))
                ->setFirstName($nameParts[0])
                ->setLastName($nameParts[1])
                ->setAvatar($this->avatarBaseUrl . strtolower(substr($nameParts[0], 0, 3)))
                ->setLoginIsVerified("1");

            // Ajouter une référence à l'utilisateur avec une clé unique
            $this->addReference('user_' . $i, $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    private function generateEmailFromName(string $name): string
    {
        $nameParts = explode(' ', $name);
        $firstName = strtolower($nameParts[0]);
        $lastName = strtolower($nameParts[1]);
        $email = $firstName . '.' . $lastName . '@gmail.com';
        return $email;
    }

    private function generateUsernameFromName(string $name): string
    {
        $nameParts = explode(' ', $name);
        $firstName = strtolower($nameParts[0]);
        $username = ucfirst(substr($firstName, 0, 3));
        return $username;
    }
}
