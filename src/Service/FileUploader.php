<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $projectDirectory;
    private $slugger;

    public function __construct($projectDirectory, SluggerInterface $slugger)
    {
        $this->projectDirectory = $projectDirectory;
        $this->slugger = $slugger;
    }

    public function uploadImage(UploadedFile $file, $fileDirectory)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.md5(uniqid()).'.'.$file->guessExtension();

        try {
            $file->move($this->getProjectDirectory().'/'.$fileDirectory, $fileName);
        } catch (FileException $e) {
            $e->getMessage();
        }

        return $fileName;
    }

    public function getProjectDirectory()
    {
        return $this->projectDirectory;
    }

}