<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Question;
use App\Entity\Bonus;
use App\Entity\Profil;
use App\Entity\Type;
use App\Entity\Contact;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
// use Symfony\Component\Mime\Attachment;

use App\Service\FileUploader;
use App\Service\Mailer;

use Dompdf\Dompdf;

// use PHPMailer\PHPMailer\PHPMailer;

class QuizzController extends AbstractController
{

    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Route("/quizz", name="app_quizz")
     */
    public function index(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        // $Quizz = $manager->getRepository(Question::class)->findAll();
        $Types = $manager->getRepository(Type::class)->findAll(); 
        $Bonus = $manager->getRepository(Bonus::class)->findAll();
        // $Quest = [];
        // $Type = [];
        // $Resp = [];
        // foreach($Quizz as $quizz){
        //     $Quest[] = $manager->getRepository(Question::class)->findBy(['Type' => $quizz->getType()]);
        //     $Type[] = $manager->getRepository(Type::class)->find($quizz->getType()->getId());
        //     $Resp[] = array_merge($Quest, $Type);
        //     $Quest = [];
        //     $Type = [];
        // }
        // dd($Resp);
        $Type = [];
        $rootDir = $this->kernel->getProjectDir();
        foreach($Types as $type){
            $Type [] =
                [
                    'id' => $type->getId(),
                    'designation' => $type->getDesignation(),
                    'image' => $type->getImage(),
                    'type' => $type->getType(),
                    'reponse' => $type->getReponse()
                ];
        }
        return $this->render('quizz/index.html.twig', [
            // 'Quizz' => $Quizz,
            'Type' => $Type,
            'Bonus' => $Bonus
        ]);
    }

    /**
     * @Route("/quizz_result", name="app_quizz_result")
     */
    public function quizz_result(Request $request): JsonResponse {

        $manager = $this->getDoctrine()->getManager();
        $Quizz = $manager->getRepository(Question::class)->findAll();
        // $Bonus = $manager->getRepository(Bonus::class)->findAll();
        
        $result = [];
        $arr = [];
        foreach($Quizz as $q){
            $result = $request->request->get('flexRadioDefault'.$q->getId());
            $result = explode("-", $result);
            $result = ['type' => $result[0], 'note' => $result[1]];
            $arr[] = $result;
        }
        $newarray = array();
        foreach ($arr as $ar) {
            foreach ($ar as $k => $v) {
                if (array_key_exists($v, $newarray)) {
                    $newarray[$v]['note'] = $newarray[$v]['note'] + $ar['note'];
                } elseif ($k == 'type') {
                    $newarray[$v] = $ar;
                }
            }
        }
        // $result2 = [];
        // $arr2 = [];
        // foreach($Bonus as $b){
        //     $result2 = $request->request->get('flexRadioDefaultBonus'.$b->getId());
        //     $result2 = explode("-", $result2);
        //     $result2 = ['type' => $result2[0], 'note' => $result2[1]];
        //     $arr2[] = $result2;
        // }
        
        // $newarray2 = array();
        // foreach($arr2 as $ar2)
        // {
        //     foreach($ar2 as $k => $v)
        //     {
        //         if(array_key_exists($v, $newarray2))
        //             $newarray2[$v]['note'] = $newarray2[$v]['note'] + $ar2['note'];
        //         else if($k == 'type')
        //             $newarray2[$v] = $ar2;
        //     }
        // }

        $tableau_combine = array();

        foreach($newarray as $element) {
            $tableau_combine[] = $element;
        }

        // foreach($newarray2 as $element) {
        //     $tableau_combine[] = $element;
        // }

        // Tableau associatif pour stocker les notes par type
        $notes_par_type = array();

        // Parcours du tableau à deux dimensions
        for ($i = 0; $i < count($tableau_combine); $i++) {
            $type = $tableau_combine[$i]["type"];
            $note = $tableau_combine[$i]["note"];

            // Si le type n'a pas encore été rencontré, on l'ajoute au tableau associatif
            if (!array_key_exists($type, $notes_par_type)) {
                $notes_par_type[$type] = $note;
            }
            // Sinon, on ajoute la note à la note existante pour ce type
            else {
                $notes_par_type[$type] += $note;
            }
        }

        $note_final=array();
        // Affichage des résultats
        foreach ($notes_par_type as $type => $somme_notes) {
            if($somme_notes<1)
            {
                $somme_notes=0;   
            }
            else $somme_notes=1;
            
            if($type!="Autre")
            {
                //echo "Type $type : $somme_notes\n";
                $note_final[]=$somme_notes;
            }   
        }
        $note_final = implode(",", $note_final);
        
        $Profil = $manager->getRepository(Profil::class)->findOneBy(['combinaison' => $note_final]);
            
        return new JsonResponse(['profil' => $Profil->getDesignation(), 'profil_id' => $Profil->getId()]);
    }

    /**
     * @Route("/quizz_send", name="app_quizz_send")
     */
    public function quizz_send(Request $request/*, MailerInterface $mailer*/,FileUploader $fileUploader, Mailer $mailer): JsonResponse {
        if(($request->isXmlHttpRequest()) /*&& ($request->request->get('nom') != '') && ($request->request->get('email') != '') && ($request->request->get('telephone') != '') && ($request->request->get('profil_id') != '')*/){
            $nom = $request->request->get('nom');
            $email = $request->request->get('email');
            $telephone = $request->request->get('telephone');
            $profil_id = $request->request->get('profil_id');
            $manager = $this->getDoctrine()->getManager();
            $Profil = $manager->getRepository(Profil::class)->find((int)$profil_id);
            $Contact = $manager->getRepository(Contact::class)->findOneBy(['email' => $email]);
            if($Contact == null){
                $Contact = new Contact();
                $Contact->setEmail($email);
            } 
            $Contact->setNom($nom);
            $Contact->setTelephone($telephone);
            $manager->persist($Contact);
            $manager->flush();

            $rootDir = $this->kernel->getProjectDir();
            $file = $rootDir . '/public/profil/'.$Profil->getPdf();
            $mailer->sendEmailWithAttachment($email, $file);

            // $path = $fileUploader->getProjectDirectory().'/profil/titre.png';
            // $type = pathinfo($path, PATHINFO_EXTENSION);
            // $data = file_get_contents($path);
            // $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            // $path2 = $fileUploader->getProjectDirectory().'/profil/element_esprits.png';
            // $type2 = pathinfo($path2, PATHINFO_EXTENSION);
            // $data2 = file_get_contents($path2);
            // $base64_2 = 'data:image/' . $type2 . ';base64,' . base64_encode($data2);
            // $path3 = $fileUploader->getProjectDirectory().'/profil/pouvoir_ressources.png';
            // $type3 = pathinfo($path3, PATHINFO_EXTENSION);
            // $data3 = file_get_contents($path3);
            // $base64_3 = 'data:image/' . $type3 . ';base64,' . base64_encode($data3);
            // $html = $this->renderView('admin/profil/content.html.twig', [
            //     'path' => $base64, 'path2' => $base64_2, 'path3' => $base64_3
            // ]);
            // $rootDir = $this->kernel->getProjectDir();
            // $path = $rootDir . '/public/profil/';
            // $filename = $Profil->getDesignation().'_' . uniqid() . '.pdf';
            // $pdfContent = $mailer->generatePDFContent($html, $path, $filename);
            // $mailer->sendEmailWithAttachment($email, $path . $filename);
            

            // $rootDir = $this->kernel->getProjectDir();
            // // Path to your image file
            // $file = $rootDir . '/public/profil/'.$Profil->getPdf();
            // $filename = $Profil->getPdf();
            // // Envoi des en-têtes HTTP pour spécifier le type de contenu
            // header('Content-type: application/pdf');
            // header('Content-Disposition: inline; filename="' . $filename . '"');
            // header('Content-Transfer-Encoding: binary');
            // header('Accept-Ranges: bytes');
            // header('Content-Length: ' . filesize($file));
            // // Envoi du contenu du fichier PDF au navigateur
            // readfile($file);


            // $email = (new Email())
            // ->from('h.andrianjafison@gmail.com')
            // ->to('thinothariniaina@gmail.com')
            // ->subject('Profil correspondant à votre réponse')
            // ->text('This is a test email.');
            // $email->attachFromPath($file, $filename);
            // $mailer->send($email);


            // $mail = new PHPMailer();
            // $mail->isSMTP();
            // $mail->Host = 'smtp.gmail.com';
            // $mail->SMTPAuth = true;
            // $mail->Username = 'h.andrianjafison@gmail.com';
            // $mail->Password = 'mmtuqyuwgmssdrkp';
            // $mail->SMTPSecure = 'tls';
            // $mail->Port = 587;
            // $mail->setFrom('h.andrianjafison@gmail.com', 'Heritiana');
            // $mail->addAddress($email, $email);
            // // $mail->addReplyTo('reply@example.com', 'Reply-to Name');
            // $mail->Subject = 'Profil correspondant à votre réponse';
            // $mail->Body = '
            // <html>
            // <head>
            //     <title>Profil correspondant à votre réponse</title>
            //     <style>
            //         body {
            //             background-color: #f8f8f8;
            //             font-family: Arial, sans-serif;
            //             font-size: 14px;
            //             line-height: 1.5;
            //         }
            //         h1 {
            //             color: #333333;
            //             font-size: 24px;
            //         }
            //     </style>
            // </head>
            // <body>
            //     <h1>Bonjour, </h1>
            //     <p>Ceci est le profil correspondant à votre réponse.</p>
            //     <p>Cordialement.</p>
            // </body>
            // </html>
            // ';
            // $mail->addAttachment($file, $filename);
            // $mail->send();
            return new JsonResponse(['pdf' => $file, 'info' => 'Vous avez reçu un e-mail concernant le détail de vos profil.']);
        }
        return new JsonResponse(['info' => 'Veuiller vérifier toutes les informations.']);
    }


    /**
     * @Route("/", name="app_quizz_home")
     */
    public function indexHome(): Response
    {
        $user = $this->getUser();

        if (!$user) {
          //return $this->redirectToRoute('login');
	}
        $manager = $this->getDoctrine()->getManager();
        // $Quizz = $manager->getRepository(Question::class)->findAll();
        $Types = $manager->getRepository(Type::class)->findAll(); 
        $Bonus = $manager->getRepository(Bonus::class)->findAll();
        // $Quest = [];
        // $Type = [];
        // $Resp = [];
        // foreach($Quizz as $quizz){
        //     $Quest[] = $manager->getRepository(Question::class)->findBy(['Type' => $quizz->getType()]);
        //     $Type[] = $manager->getRepository(Type::class)->find($quizz->getType()->getId());
        //     $Resp[] = array_merge($Quest, $Type);
        //     $Quest = [];
        //     $Type = [];
        // }
        // dd($Resp);
        $Type = [];
        $rootDir = $this->kernel->getProjectDir();
        foreach($Types as $type){
            $Type [] =
                [
                    'id' => $type->getId(),
                    'designation' => $type->getDesignation(),
                    'image' => $type->getImage(),
                    'type' => $type->getType(),
                    'reponse' => $type->getReponse()
                ];
        }
        return $this->render('quizz/index.html.twig', [
            // 'Quizz' => $Quizz,
            'Type' => $Type,
            'Bonus' => $Bonus
        ]);
    }

}
