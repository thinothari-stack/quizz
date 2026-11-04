<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\FileUploader;

use Dompdf\Dompdf;

class PdfGeneratorController extends AbstractController
{

    /**
     * @Route("/pdf/generator", name="app_pdf_generator")
     */
    public function index(FileUploader $fileUploader): Response
    {

        $path = $fileUploader->getProjectDirectory().'/profil/titre.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        $path2 = $fileUploader->getProjectDirectory().'/profil/element_esprits.png';
        $type2 = pathinfo($path2, PATHINFO_EXTENSION);
        $data2 = file_get_contents($path2);
        $base64_2 = 'data:image/' . $type2 . ';base64,' . base64_encode($data2);

        $path3 = $fileUploader->getProjectDirectory().'/profil/pouvoir_ressources.png';
        $type3 = pathinfo($path3, PATHINFO_EXTENSION);
        $data3 = file_get_contents($path3);
        $base64_3 = 'data:image/' . $type3 . ';base64,' . base64_encode($data3);

        $dompdf = new Dompdf();
        $html = $this->renderView('admin/profil/content.html.twig', [
            'path' => $base64, 'path2' => $base64_2, 'path3' => $base64_3
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="output.pdf"']);
    }
}
