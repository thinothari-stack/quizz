<?php

namespace App\DataFixtures;

use App\Entity\Type;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\Profil;
use App\Entity\Bonus;
use App\Entity\ReponseBonus;
use App\Entity\Compte;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuizzFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $type1 = new Type();
        $type1->setDesignation('Corps');
        $manager->persist($type1);

        $type2 = new Type();
        $type2->setDesignation('Emotions');
        $manager->persist($type2);

        $type3 = new Type();
        $type3->setDesignation('Ames');
        $manager->persist($type3);

        $type4 = new Type();
        $type4->setDesignation('Autre');
        $manager->persist($type4);

        $question1 = new Question();
        $question1->setDesignation('Dans vos actions, mouvements et processus du quoditien (dont la digestion), votre corps');
        $question1->setType($type1);
        $manager->persist($question1);

        $question2 = new Question();
        $question2->setDesignation('Comment vous sentez vous dans votre corps aujourd\'hui ? ');
        $question2->setType($type1);
        $manager->persist($question2);

        $question3 = new Question();
        $question3->setDesignation('Vous êtes plutôt');
        $question3->setType($type2);
        $manager->persist($question3);

        $question4 = new Question();
        $question4->setDesignation('Vous êtes plutôt');
        $question4->setType($type2);
        $manager->persist($question4);

        $question5 = new Question();
        $question5->setDesignation('Sur 24 heures, considérez vous pour vous ?');
        $question5->setType($type3);
        $manager->persist($question5);

        $question6 = new Question();
        $question6->setDesignation('Vous êtes plutot');
        $question6->setType($type3);
        $manager->persist($question6);

        $reponse = new Reponse();
        $reponse->setDesignation('vous permet de quasiment tout faire');
        $reponse->setQuestion($question1);
        $reponse->setNote(0.5);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('vous limite');
        $reponse->setQuestion($question1);
        $reponse->setNote(0.25);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('vous handicape');
        $reponse->setQuestion($question1);
        $reponse->setNote(0);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('Mieux qu\'il y a 5 ans');
        $reponse->setQuestion($question2);
        $reponse->setNote(0.5);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('Moins bien qu\'il y a 5 ans');
        $reponse->setQuestion($question2);
        $reponse->setNote(0.25);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('je n\'observe aucune différence');
        $reponse->setQuestion($question2);
        $reponse->setNote(0);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('l\'hiver c\'est bien aussi');
        $reponse->setQuestion($question3);
        $reponse->setNote(0.5);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('du soleil toute l\'année');
        $reponse->setQuestion($question3);
        $reponse->setNote(0.25);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('les saisons n\'impactent pas ma vie');
        $reponse->setQuestion($question3);
        $reponse->setNote(0);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('Je pleure comme je ris');
        $reponse->setQuestion($question4);
        $reponse->setNote(0.5);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('Sorties et fêtes entre amis');
        $reponse->setQuestion($question4);
        $reponse->setNote(0.25);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('Confiné plaid et canapé');
        $reponse->setQuestion($question4);
        $reponse->setNote(0);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('plus de 16h');
        $reponse->setQuestion($question5);
        $reponse->setNote(0.5);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('8h à 16H');
        $reponse->setQuestion($question5);
        $reponse->setNote(0.25);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('0-8h');
        $reponse->setQuestion($question5);
        $reponse->setNote(0);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('Le hasard n\'existe pas');
        $reponse->setQuestion($question6);
        $reponse->setNote(0.5);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('fasciné par les sciences de la vie (astronomie, médecine, philosphie, quantique)');
        $reponse->setQuestion($question6);
        $reponse->setNote(0.25);
        $manager->persist($reponse);

        $reponse = new Reponse();
        $reponse->setDesignation('Je crois que ce que je vois');
        $reponse->setQuestion($question6);
        $reponse->setNote(0);
        $manager->persist($reponse);

        $profil = new Profil();
        $profil->setDesignation('Fleur Blanche');
        $profil->setCombinaison('1,1,1');
        $profil->setPdf('Fleur_Blanche.pdf');
        $manager->persist($profil);

        $profil = new Profil();
        $profil->setDesignation('Fumée Grise');
        $profil->setCombinaison('0,0,0');
        $profil->setPdf('Fumee_Grise.pdf');
        $manager->persist($profil);

        $profil = new Profil();
        $profil->setDesignation('Sable Rouge');
        $profil->setCombinaison('1,0,0');
        $profil->setPdf('Sable_Rouge.pdf');
        $manager->persist($profil);

        $profil = new Profil();
        $profil->setDesignation('Graine Mauve');
        $profil->setCombinaison('1,1,0');
        $profil->setPdf('Graine_Mauve.pdf');
        $manager->persist($profil);

        $profil = new Profil();
        $profil->setDesignation('Feuille Orange');
        $profil->setCombinaison('1,0,1');
        $profil->setPdf('Feuille_Orange.pdf');
        $manager->persist($profil);

        $profil = new Profil();
        $profil->setDesignation('Bulle Bleue');
        $profil->setCombinaison('0,1,0');
        $profil->setPdf('Bulle_Bleue.pdf');
        $manager->persist($profil);

        $profil = new Profil();
        $profil->setDesignation('Nuage Vert');
        $profil->setCombinaison('0,1,1');
        $profil->setPdf('Nuage_Vert.pdf');
        $manager->persist($profil);

        $profil = new Profil();
        $profil->setDesignation('Lueur Jaune');
        $profil->setCombinaison('0,0,1');
        $profil->setPdf('Lueur_Jaune.pdf');
        $manager->persist($profil);

        $bonus1 = new Bonus();
        $bonus1->setDesignation('Ce qui vous ressource le plus');
        $manager->persist($bonus1);

        $bonus2 = new Bonus();
        $bonus2->setDesignation('Choisissez un nuancier de couleurs');
        $manager->persist($bonus2);

        $reponseBonus = new ReponseBonus();
        $reponseBonus->setDesignation('Dormir');
        $reponseBonus->setBonus($bonus1);
        $reponseBonus->setType($type1);
        $reponseBonus->setNote(0.5);
        $manager->persist($reponseBonus);

        $reponseBonus = new ReponseBonus();
        $reponseBonus->setDesignation('blanc gris noir');
        $reponseBonus->setBonus($bonus2);
        $reponseBonus->setType($type1);
        $reponseBonus->setImage('noir_et_blanc.png');
        $reponseBonus->setNote(0.5);
        $manager->persist($reponseBonus);

        $reponseBonus = new ReponseBonus();
        $reponseBonus->setDesignation('Manger');
        $reponseBonus->setBonus($bonus1);
        $reponseBonus->setType($type2);
        $reponseBonus->setNote(0.5);
        $manager->persist($reponseBonus);

        $reponseBonus = new ReponseBonus();
        $reponseBonus->setDesignation('jaune orange rouge');
        $reponseBonus->setBonus($bonus2);
        $reponseBonus->setType($type2);
        $reponseBonus->setImage('rouge_orange_jaune.png');
        $reponseBonus->setNote(0.5);
        $manager->persist($reponseBonus);

        $reponseBonus = new ReponseBonus();
        $reponseBonus->setDesignation('Pratiquer votre passion');
        $reponseBonus->setBonus($bonus1);
        $reponseBonus->setType($type3);
        $reponseBonus->setNote(0.5);
        $manager->persist($reponseBonus);

        $reponseBonus = new ReponseBonus();
        $reponseBonus->setDesignation('bleu violet');
        $reponseBonus->setBonus($bonus2);
        $reponseBonus->setType($type3);
        $reponseBonus->setImage('violet_bleu.png');
        $reponseBonus->setNote(0.5);
        $manager->persist($reponseBonus);

        $reponseBonus = new ReponseBonus();
        $reponseBonus->setDesignation('Je ne sais pas');
        $reponseBonus->setType($type4);
        $reponseBonus->setBonus($bonus1);
        $reponseBonus->setNote(0);
        $manager->persist($reponseBonus);

        $reponseBonus = new ReponseBonus();
        $reponseBonus->setDesignation('arc en ciel');
        $reponseBonus->setBonus($bonus2);
        $reponseBonus->setType($type4);
        $reponseBonus->setImage('arc_en_ciel.png');
        $reponseBonus->setNote(0);
        $manager->persist($reponseBonus);

        $adminPassword = $_SERVER['QUIZZ_ADMIN_PASSWORD'] ?? $_ENV['QUIZZ_ADMIN_PASSWORD'] ?? null;
        if (is_string($adminPassword) && $adminPassword !== '') {
            $compte = new Compte();
            $compte->setNom($_SERVER['QUIZZ_ADMIN_NAME'] ?? $_ENV['QUIZZ_ADMIN_NAME'] ?? 'Administrator');
            $compte->setEmail($_SERVER['QUIZZ_ADMIN_EMAIL'] ?? $_ENV['QUIZZ_ADMIN_EMAIL'] ?? 'admin@example.test');
            $compte->setRoles(['ROLE_ADMIN']);
            $compte->setUsername($_SERVER['QUIZZ_ADMIN_USERNAME'] ?? $_ENV['QUIZZ_ADMIN_USERNAME'] ?? 'admin');
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                throw new \RuntimeException('Unable to hash the configured quiz administrator password.');
            }
            $compte->setPassword($hashedPassword);
            $manager->persist($compte);
        }

        $manager->flush();
    }
}
