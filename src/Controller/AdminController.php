<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Compte;
use App\Entity\Type;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\Bonus;
use App\Entity\ReponseBonus;
use App\Entity\Profil;
use App\Entity\Contact;

use App\Service\FileUploader;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;

class AdminController extends AbstractController
{

    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    // /**
    //  * @Route("/", name="app_admin_login")
    //  */
    // public function login(): Response
    // {
    //     return $this->render('admin/compte/login.html.twig', [
    //     ]);
    // }

    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'dashboard' => true
        ]);
    }

    /**
     * @Route("/admin/compte", name="app_admin_compte")
     */
    public function compte(): Response
    {
        return $this->render('admin/compte/index.html.twig', [
            
        ]);
    }

    /**
     * @Route("/admin/compte/list", name="app_admin_compte_list")
     */
    public function compte_list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $Compte = $manager->getRepository(Compte::class)->findAll();
        return $this->render('admin/compte/list.html.twig', [
            'Compte' => $Compte
        ]);
    }
    

    /**
     * @Route("/admin/type", name="app_admin_type")
     */
    public function type(Request $request, FileUploader $fileUploader)
    {
        if ( $request->isXmlHttpRequest() ) {
            $manager = $this->getDoctrine()->getManager();
            $Type = new Type();
            $Type->setDesignation($request->request->get('designation'));
            if($request->files->get('image') != ''){
                $output = $fileUploader->uploadImage($request->files->get('image'),'type');
                $Type->setImage($output);
            } else{
                $Type->setImage("");
            }
            $manager->persist($Type);
            $manager->flush();
            $info = "Type mis à jour avec succès";
            return new JsonResponse(['info' => $info]);    
        }
        return $this->render('admin/type/index.html.twig', [
            'type' => true
        ]);
    }

    /**
     * @Route("/admin/type/list", name="app_admin_type_list")
     */
    public function type_list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $Type = $manager->getRepository(Type::class)->findAll();
        return $this->render('admin/type/list.html.twig', [
            'Type' => $Type
        ]);
    }

    /**
     * @Route("/admin/type/edit", name="app_admin_type_edit")
     */
    public function type_edit(Request $request, Filesystem $filesystem, FileUploader $fileUploader)
    {
        $manager = $this->getDoctrine()->getManager();
        $Type = $manager->getRepository(Type::class)->find((int)$request->request->get('idType'));
        $rootDir = $this->kernel->getProjectDir();
        $file = $rootDir . '/public/type/'.$Type->getImage();
        $filesystem->remove($file);
        $Type->setDesignation($request->request->get('designation'));
        if($request->files->get('image') != ''){
            $output = $fileUploader->uploadImage($request->files->get('image'),'type');
            $Type->setImage($output);
        } else{
            $Type->setImage("");
        }
        $manager->persist($Type);
        $manager->flush();
        return new JsonResponse(['info' => "Type modifié avec succès"]); 
    }

    /**
     * @Route("/admin/type/delete", name="app_admin_type_delete")
     */
    public function type_delete(Request $request, Filesystem $filesystem)
    {
        $manager = $this->getDoctrine()->getManager();
        $Type = $manager->getRepository(Type::class)->find((int)$request->request->get('idType'));
        $rootDir = $this->kernel->getProjectDir();
        $file = $rootDir . '/public/type/'.$Type->getImage();
        $filesystem->remove($file);
        $manager->remove($Type);
        $manager->flush();
        return new JsonResponse(['info' => "Type supprimé avec succès"]); 
    }

    /**
     * @Route("/admin/profil/ajax/{id}", name="app_admin_profil_ajax")
     */
    public function type_ajax_get_picture($id, FileUploader $fileUploader) {
        $manager = $this->getDoctrine()->getManager();
        $Profil = $manager->getRepository(Profil::class)->find((int)$id);
        $rootDir = $this->kernel->getProjectDir();
        $path = $rootDir . '/public/profil/'.$Profil->getImage();
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return new JsonResponse(['image' => $Profil->getImage(), 'path' => $base64]);
    }

    /**
     * @Route("/admin/question", name="app_admin_question")
     */
    public function question(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        if ( $request->isXmlHttpRequest() ) {
            $Question = new Question();
            $Type = $manager->getRepository(Type::class)->find((int)$request->request->get('Type'));
            $Question->setDesignation($request->request->get('designation'));
            $Question->setType($Type);
            $manager->persist($Question);
            $manager->flush();
            $info = "Question mis à jour avec succès";
            return new JsonResponse(['info' => $info]);    
        }
        $Type = $manager->getRepository(Type::class)->findAll();
        return $this->render('admin/question/index.html.twig', [
            'question' => true,
            'Type' => $Type
        ]);
    }

    /**
     * @Route("/admin/question/list", name="app_admin_question_list")
     */
    public function question_list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $Type = $manager->getRepository(Type::class)->findAll();
        $Question = $manager->getRepository(Question::class)->findAll();
        return $this->render('admin/question/list.html.twig', [
            'Type' => $Type,
            'Question' => $Question
        ]);
    }

    /**
     * @Route("/admin/question/edit", name="app_admin_question_edit")
     */
    public function question_edit(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $Question = $manager->getRepository(Question::class)->find((int)$request->request->get('idQuestion'));
        $Type = $manager->getRepository(Type::class)->find((int)$request->request->get('Type'));
        $Question->setType($Type);
        $Question->setDesignation($request->request->get('designation'));
        $manager->persist($Question);
        $manager->flush();
        return new JsonResponse(['info' => "Question modifié avec succès"]); 
    }

    /**
     * @Route("/admin/question/delete", name="app_admin_question_delete")
     */
    public function question_delete(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $Question = $manager->getRepository(Question::class)->find((int)$request->request->get('idQuestion'));
        $manager->remove($Question);
        $manager->flush();
        return new JsonResponse(['info' => "Question supprimé avec succès"]); 
    }

    /**
     * @Route("/admin/reponse", name="app_admin_reponse")
     */
    public function reponse(Request $request, FileUploader $fileUploader)
    {
        $manager = $this->getDoctrine()->getManager();
        if ( $request->isXmlHttpRequest() ) {
            $Reponse = new Reponse();
            $Question = $manager->getRepository(Question::class)->find((int)$request->request->get('Question'));
            $Reponse->setQuestion($Question);
            $Reponse->setDesignation($request->request->get('designation'));
            $Reponse->setNote((float)$request->request->get('note'));
            if($request->files->get('image') != ''){
                $output = $fileUploader->uploadImage($request->files->get('image'),'reponse');
                $Reponse->setImage($output);
            } else{
                $Reponse->setImage("");
            }
            $manager->persist($Reponse);
            $manager->flush();
            $info = "Réponse mis à jour avec succès";
            return new JsonResponse(['info' => $info]);    
        }
        $Question = $manager->getRepository(Question::class)->findAll();
        return $this->render('admin/reponse/index.html.twig', [
            'reponse' => true,
            'Question' => $Question
        ]);
    }

    /**
     * @Route("/admin/reponse/list", name="app_admin_reponse_list")
     */
    public function reponse_list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $Question = $manager->getRepository(Question::class)->findAll();
        $Reponse = $manager->getRepository(Reponse::class)->findAll();
        return $this->render('admin/reponse/list.html.twig', [
            'Question' => $Question,
            'Reponse' => $Reponse
        ]);
    }

    /**
     * @Route("/admin/reponse/edit", name="app_admin_reponse_edit")
     */
    public function reponse_edit(Request $request, Filesystem $filesystem, FileUploader $fileUploader)
    {
        $manager = $this->getDoctrine()->getManager();
        $Reponse = $manager->getRepository(Reponse::class)->find((int)$request->request->get('idReponse'));
        $Question = $manager->getRepository(Question::class)->find((int)$request->request->get('Question'));
        $rootDir = $this->kernel->getProjectDir();
        $file = $rootDir . '/public/reponse/'.$Reponse->getImage();
        $filesystem->remove($file);
        $Reponse->setQuestion($Question);
        $Reponse->setDesignation($request->request->get('designation'));
        $Reponse->setNote((float)$request->request->get('note'));
        if($request->files->get('image') != ''){
            $output = $fileUploader->uploadImage($request->files->get('image'),'reponse');
            $Reponse->setImage($output);
        } else{
            $Reponse->setImage("");
        }
        $manager->persist($Reponse);
        $manager->flush();
        return new JsonResponse(['info' => "Réponse modifié avec succès"]); 
    }

    /**
     * @Route("/admin/reponse/delete", name="app_admin_reponse_delete")
     */
    public function reponse_delete(Request $request, Filesystem $filesystem)
    {
        $manager = $this->getDoctrine()->getManager();
        $Reponse = $manager->getRepository(Reponse::class)->find((int)$request->request->get('idReponse'));
        $rootDir = $this->kernel->getProjectDir();
        $file = $rootDir . '/public/reponse/'.$Reponse->getImage();
        $filesystem->remove($file);
        $manager->remove($Reponse);
        $manager->flush();
        return new JsonResponse(['info' => "Réponse supprimé avec succès"]); 
    }

    /**
     * @Route("/admin/bonus", name="app_admin_bonus")
     */
    public function bonus(Request $request)
    {
        if ( $request->isXmlHttpRequest() ) {
            $manager = $this->getDoctrine()->getManager();
            $Bonus = new Bonus();
            $Bonus->setDesignation($request->request->get('designation'));
            $manager->persist($Bonus);
            $manager->flush();
            $info = "Bonus mis à jour avec succès";
            return new JsonResponse(['info' => $info]);    
        }
        return $this->render('admin/bonus/index.html.twig', [
            'bonus' => true
        ]);
    }

    /**
     * @Route("/admin/bonus/list", name="app_admin_bonus_list")
     */
    public function bonus_list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $Bonus = $manager->getRepository(Bonus::class)->findAll();
        return $this->render('admin/bonus/list.html.twig', [
            'Bonus' => $Bonus
        ]);
    }

    /**
     * @Route("/admin/bonus/edit", name="app_admin_bonus_edit")
     */
    public function bonus_edit(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $Bonus = $manager->getRepository(Bonus::class)->find((int)$request->request->get('idBonus'));
        $Bonus->setDesignation($request->request->get('designation'));
        $manager->persist($Bonus);
        $manager->flush();
        return new JsonResponse(['info' => "Bonus modifié avec succès"]); 
    }

    /**
     * @Route("/admin/bonus/delete", name="app_admin_bonus_delete")
     */
    public function bonus_delete(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $Bonus = $manager->getRepository(Bonus::class)->find((int)$request->request->get('idBonus'));
        $manager->remove($Bonus);
        $manager->flush();
        return new JsonResponse(['info' => "Bonus supprimé avec succès"]); 
    }

    /**
     * @Route("/admin/reponse_bonus", name="app_admin_reponse_bonus")
     */
    public function reponse_bonus(Request $request, FileUploader $fileUploader)
    {
        $manager = $this->getDoctrine()->getManager();
        if ( $request->isXmlHttpRequest() ) {
            $ReponseBonus = new ReponseBonus();
            $Bonus = $manager->getRepository(Bonus::class)->find((int)$request->request->get('Bonus'));
            $Type = $manager->getRepository(Type::class)->find((int)$request->request->get('Type'));
            $ReponseBonus->setBonus($Bonus);
            $ReponseBonus->setType($Type);
            $ReponseBonus->setDesignation($request->request->get('designation'));
            $ReponseBonus->setNote((float)$request->request->get('note'));
            if($request->files->get('image') != ''){
                $output = $fileUploader->uploadImage($request->files->get('image'),'reponse_bonus');
                $ReponseBonus->setImage($output);
            } else{
                $ReponseBonus->setImage("");
            }
            $manager->persist($ReponseBonus);
            $manager->flush();
            $info = "Réponse bonus mis à jour avec succès";
            return new JsonResponse(['info' => $info]);    
        }
        $Type = $manager->getRepository(Type::class)->findAll();
        $Bonus = $manager->getRepository(Bonus::class)->findAll();
        return $this->render('admin/reponse_bonus/index.html.twig', [
            'reponse_bonus' => true,
            'Type' => $Type,
            'Bonus'=> $Bonus
        ]);
    }

    /**
     * @Route("/admin/reponse_bonus/list", name="app_admin_reponse_bonus_list")
     */
    public function reponse_bonus_list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $Bonus = $manager->getRepository(Bonus::class)->findAll();
        $Type = $manager->getRepository(Type::class)->findAll();
        $ReponseBonus = $manager->getRepository(ReponseBonus::class)->findAll();
        return $this->render('admin/reponse_bonus/list.html.twig', [
            'Bonus' => $Bonus,
            'Type' => $Type,
            'ReponseBonus' => $ReponseBonus
        ]);
    }

    /**
     * @Route("/admin/reponse_bonus/edit", name="app_admin_reponse_bonus_edit")
     */
    public function reponse_bonus_edit(Request $request, Filesystem $filesystem, FileUploader $fileUploader)
    {
        $manager = $this->getDoctrine()->getManager();
        $ReponseBonus = $manager->getRepository(ReponseBonus::class)->find((int)$request->request->get('idReponseBonus'));
        $Bonus = $manager->getRepository(Bonus::class)->find((int)$request->request->get('Bonus'));
        $Type = $manager->getRepository(Type::class)->find((int)$request->request->get('Type'));
        $rootDir = $this->kernel->getProjectDir();
        $file = $rootDir . '/public/reponse_bonus/'.$ReponseBonus->getImage();
        $filesystem->remove($file);
        $ReponseBonus->setBonus($Bonus);
        $ReponseBonus->setType($Type);
        $ReponseBonus->setDesignation($request->request->get('designation'));
        $ReponseBonus->setNote((float)$request->request->get('note'));
        if($request->files->get('image') != ''){
            $output = $fileUploader->uploadImage($request->files->get('image'),'reponse_bonus');
            $ReponseBonus->setImage($output);
        } else{
            $ReponseBonus->setImage("");
        }
        $manager->persist($ReponseBonus);
        $manager->flush();
        return new JsonResponse(['info' => "Réponse bonus modifié avec succès"]); 
    }

    /**
     * @Route("/admin/reponse_bonus/delete", name="app_admin_reponse_bonus_delete")
     */
    public function reponse_bonus_delete(Request $request, Filesystem $filesystem)
    {
        $manager = $this->getDoctrine()->getManager();
        $ReponseBonus = $manager->getRepository(ReponseBonus::class)->find((int)$request->request->get('idReponseBonus'));
        $rootDir = $this->kernel->getProjectDir();
        $file = $rootDir . '/public/reponse_bonus/'.$ReponseBonus->getImage();
        $filesystem->remove($file);
        $manager->remove($ReponseBonus);
        $manager->flush();
        return new JsonResponse(['info' => "Réponse bonus supprimé avec succès"]); 
    }

    /**
     * @Route("/quizz/profil", name="app_quizz_profil")
     */
    public function quizz_profil(): Response
    {
        return $this->render('admin/profil/profil.html.twig');
    }

    /**
     * @Route("/admin/profil", name="app_admin_profil")
     */
    public function profil(Request $request, FileUploader $fileUploader)
    {
        if ( $request->isXmlHttpRequest() ) {
            $manager = $this->getDoctrine()->getManager();
            $Profil = new Profil();
            $Profil->setDesignation($request->request->get('designation'));
            if($request->files->get('image') != ''){
                $output = $fileUploader->uploadImage($request->files->get('image'),'profil');
                $Profil->setImage($output);
            } else{
                $Profil->setImage("");
            }
            $manager->persist($Profil);
            $manager->flush();
            $info = "Profil mis à jour avec succès";
            return new JsonResponse(['info' => $info]);    
        }
        return $this->render('admin/profil/index.html.twig', [
            'profil' => true
        ]);
    }

    /**
     * @Route("/admin/profil/list", name="app_admin_profil_list")
     */
    public function profil_list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $Profil = $manager->getRepository(Profil::class)->findAll();
        return $this->render('admin/profil/list.html.twig', [
            'Profil' => $Profil
        ]);
    }

    /**
     * @Route("/admin/profil/edit", name="app_admin_profil_edit")
     */
    public function profil_edit(Request $request, Filesystem $filesystem, FileUploader $fileUploader)
    {
        $manager = $this->getDoctrine()->getManager();
        $Profil = $manager->getRepository(Profil::class)->find((int)$request->request->get('idProfil'));
        $rootDir = $this->kernel->getProjectDir();
        $file = $rootDir . '/public/profil/'.$Profil->getImage();
        $filesystem->remove($file);
        $Profil->setDesignation($request->request->get('designation'));
        if($request->files->get('image') != ''){
            $output = $fileUploader->uploadImage($request->files->get('image'),'profil');
            $Profil->setImage($output);
        } else{
            $Profil->setImage("");
        }
        $manager->persist($Profil);
        $manager->flush();
        return new JsonResponse(['info' => "Profil modifié avec succès"]); 
    }

    /**
     * @Route("/admin/profil/delete", name="app_admin_profil_delete")
     */
    public function profil_delete(Request $request, Filesystem $filesystem)
    {
        $manager = $this->getDoctrine()->getManager();
        $Profil = $manager->getRepository(Profil::class)->find((int)$request->request->get('idProfil'));
        $rootDir = $this->kernel->getProjectDir();
        $file = $rootDir . '/public/profil/'.$Profil->getImage();
        $filesystem->remove($file);
        $manager->remove($Profil);
        $manager->flush();
        return new JsonResponse(['info' => "Profil supprimé avec succès"]); 
    }

    /**
     * @Route("/admin/contact", name="app_admin_contact")
     */
    public function contact(Request $request)
    {
        if ( $request->isXmlHttpRequest() ) {
            $manager = $this->getDoctrine()->getManager();
            $Contact = new Contact();
            $Contact->setEmail($request->request->get('email'));
            $Contact->setNom($request->request->get('nom'));
            $Contact->setTelephone($request->request->get('telephone'));
            $manager->persist($Contact);
            $manager->flush();
            $info = "Contact mis à jour avec succès";
            return new JsonResponse(['info' => $info]);    
        }
        return $this->render('admin/contact/index.html.twig', [
            'contact' => true
        ]);
    }

    /**
     * @Route("/admin/contact/list", name="app_admin_contact_list")
     */
    public function contact_list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $Contact = $manager->getRepository(Contact::class)->findAll();
        return $this->render('admin/contact/list.html.twig', [
            'Contact' => $Contact
        ]);
    }

    /**
     * @Route("/admin/contact/edit", name="app_admin_contact_edit")
     */
    public function contact_edit(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $Contact = $manager->getRepository(Contact::class)->find((int)$request->request->get('idContact'));
        $Contact->setEmail($request->request->get('email'));
        $Contact->setNom($request->request->get('nom'));
        $Contact->setTelephone($request->request->get('telephone'));
        $manager->persist($Contact);
        $manager->flush();
        return new JsonResponse(['info' => "Contact modifié avec succès"]); 
    }

    /**
     * @Route("/admin/contact/delete", name="app_admin_contact_delete")
     */
    public function contact_delete(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $Contact = $manager->getRepository(Contact::class)->find((int)$request->request->get('idContact'));
        $rootDir = $this->kernel->getProjectDir();
        $manager->remove($Contact);
        $manager->flush();
        return new JsonResponse(['info' => "Contact supprimé avec succès"]); 
    }
}
