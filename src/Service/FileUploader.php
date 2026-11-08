<?php

namespace App\Service;

use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $projectDirectory;
    private $slugger;

    public function __construct(string $projectDirectory, SluggerInterface $slugger)
    {
        $this->projectDirectory = $projectDirectory;
        $this->slugger = $slugger;
    }

    public function uploadImage(UploadedFile $file, string $fileDirectory): string
    {
        if (!preg_match('/\A[a-zA-Z0-9_-]+\z/', $fileDirectory)) {
            throw new \InvalidArgumentException('The upload directory must be a single safe path segment.');
        }

        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) {
            throw new \InvalidArgumentException('Only JPEG, PNG, GIF, and WebP images can be uploaded.');
        }

        $extension = $file->guessExtension();
        if ($extension === null) {
            throw new RuntimeException('Unable to determine the uploaded image file extension.');
        }

        $directory = $this->projectDirectory . DIRECTORY_SEPARATOR . $fileDirectory;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create upload directory "%s".', $directory));
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $filename = sprintf(
            '%s-%s.%s',
            $safeFilename !== '' ? $safeFilename : 'image',
            bin2hex(random_bytes(16)),
            $extension
        );

        $file->move($directory, $filename);

        return $filename;
    }

    public function getProjectDirectory(): string
    {
        return $this->projectDirectory;
    }
}
