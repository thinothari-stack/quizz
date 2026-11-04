<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
// use Symfony\Component\Mime\Attachment;
use Dompdf\Dompdf;
use Dompdf\Options;

class Mailer
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmailWithAttachment($recipientEmail, $pdfContent)
    {
        $email = (new Email())
            ->from('h.andrianjafison@gmail.com')
            ->to($recipientEmail)
            ->subject('Decouvrez le profil correspondant a votre reponse !')
            ->html('
            <html lang="fr">
            <head>
                <meta charset="utf-8">
                <title>Decouvrez le profil correspondant a votre reponse !</title>
                <style>
                    body {
                        background-color: #f8f8f8;
                        font-family: Arial, sans-serif;
                        font-size: 14px;
                        line-height: 1.5;
                    }
                    h1 {
                        color: #333333;
                        font-size: 24px;
                    }
                </style>
            </head>
            <body>
                <h1>Bonjour, </h1> 
                <p>Merci pour votre participation au test Rep\'Ose, voici le profil correspondant à l\'élément-esprit qui accompagne vos moments de repos actuellement.</p> 
                <p>Suite à ce test, vous êtes abonné.e à la newsletter mensuelle dans laquelle vous recevrez chaque mois des informations sur les types de repos et leurs pouvoirs.</p>
                <p>Si vous souhaitez aller plus loin, telle une fée sur votre chemin, je me place à vos côtés pour vous accompagner dans la réalisation de vos objectifs vers plus d\'apaisement et de joie de vivre. Massages à domicile, consultations, libération émotionnelle (EFT) et outils d’éveil à l’introspection sont à votre service pour développer vos pouvoirs-ressources intérieurs.</p>
                <p>Plus d’informations sur la page Aventure Rep’Ose du site internet en cliquant ici  (voici le lien à intégrer : https://temporepos.fr/aventure_repose/)</p>
                <p>En vous souhaitant de belles explorations sur la voie de votre repos, </p>
                <p>Quiz Temp’Ô Repos</p>
            </body>
            </html>
            ');
        $email->attachFromPath($pdfContent, 'profil.pdf');
        $this->mailer->send($email);
    }

    public function generatePDFContent($html, $path,$filename)
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents($path . $filename, $output);
        // return new Response('PDF file generated and saved.');
    }
}
