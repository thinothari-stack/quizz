<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mailer
{
    private $mailer;
    private $senderEmail;

    public function __construct(MailerInterface $mailer, string $senderEmail)
    {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
    }

    public function sendEmailWithAttachment(string $recipientEmail, string $pdfPath): void
    {
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The recipient email address is invalid.');
        }

        if (!is_file($pdfPath) || !is_readable($pdfPath)) {
            throw new RuntimeException(sprintf('The PDF attachment "%s" does not exist or is not readable.', $pdfPath));
        }

        $email = (new Email())
            ->from($this->senderEmail)
            ->to($recipientEmail)
            ->subject('Découvrez le profil correspondant à votre réponse !')
            ->text(
                "Bonjour,\n\n"
                . "Merci pour votre participation au test Rep'Ose. Voici le profil correspondant "
                . "à l'élément-esprit qui accompagne actuellement vos moments de repos.\n\n"
                . "Suite à ce test, vous êtes abonné·e à la newsletter mensuelle, dans laquelle "
                . "vous recevrez chaque mois des informations sur les types de repos et leurs pouvoirs.\n\n"
                . "Si vous souhaitez aller plus loin, je me place à vos côtés pour vous accompagner "
                . "dans la réalisation de vos objectifs vers plus d'apaisement et de joie de vivre. "
                . "Massages à domicile, consultations, libération émotionnelle (EFT) et outils d'éveil "
                . "à l'introspection sont à votre service pour développer vos pouvoirs-ressources intérieurs.\n\n"
                . "Plus d'informations sur la page Aventure Rep'Ose : https://temporepos.fr/aventure_repose/\n\n"
                . "En vous souhaitant de belles explorations sur la voie de votre repos,\n"
                . "Quiz Temp'Repos"
            )
            ->html(
                '<html lang="fr"><head><meta charset="utf-8"><title>'
                . 'Découvrez le profil correspondant à votre réponse !'
                . '</title></head><body style="font-family:Arial,sans-serif;font-size:14px;line-height:1.5">'
                . '<h1>Bonjour,</h1>'
                . '<p>Merci pour votre participation au test Rep\'Ose. Voici le profil correspondant '
                . 'à l\'élément-esprit qui accompagne actuellement vos moments de repos.</p>'
                . '<p>Suite à ce test, vous êtes abonné·e à la newsletter mensuelle, dans laquelle '
                . 'vous recevrez chaque mois des informations sur les types de repos et leurs pouvoirs.</p>'
                . '<p>Si vous souhaitez aller plus loin, je me place à vos côtés pour vous accompagner '
                . 'dans la réalisation de vos objectifs vers plus d\'apaisement et de joie de vivre. '
                . 'Massages à domicile, consultations, libération émotionnelle (EFT) et outils d\'éveil '
                . 'à l\'introspection sont à votre service pour développer vos pouvoirs-ressources intérieurs.</p>'
                . '<p>Plus d\'informations sur la page '
                . '<a href="https://temporepos.fr/aventure_repose/">Aventure Rep\'Ose</a>.</p>'
                . '<p>En vous souhaitant de belles explorations sur la voie de votre repos,<br>'
                . 'Quiz Temp\'Repos</p>'
                . '</body></html>'
            )
            ->attachFromPath($pdfPath, 'profil.pdf', 'application/pdf');

        $this->mailer->send($email);
    }

    public function generatePDFContent(string $html, string $directory, string $filename): string
    {
        if (basename($filename) !== $filename || $filename === '' || $filename === '.' || $filename === '..') {
            throw new \InvalidArgumentException('The PDF filename must not contain a directory path.');
        }

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        $directory = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create PDF directory "%s".', $directory));
        }

        $path = $directory . $filename;
        if (file_put_contents($path, $dompdf->output()) === false) {
            throw new RuntimeException(sprintf('Unable to write PDF file "%s".', $path));
        }

        return $path;
    }
}
