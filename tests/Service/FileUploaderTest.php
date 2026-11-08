<?php

namespace App\Tests\Service;

use App\Service\FileUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

class FileUploaderTest extends TestCase
{
    private $temporaryDirectory;

    protected function setUp(): void
    {
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'quizz-uploader-' . bin2hex(random_bytes(8));
        mkdir($this->temporaryDirectory, 0775, true);
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->temporaryDirectory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($this->temporaryDirectory);
    }

    public function testUploadImageCreatesAFileInTheRequestedDirectory(): void
    {
        $source = $this->createPngUpload('Mon profil.png');
        $uploader = new FileUploader($this->temporaryDirectory, new AsciiSlugger());

        $filename = $uploader->uploadImage($source, 'profil');

        self::assertMatchesRegularExpression('/\A[A-Za-z0-9-]+-[a-f0-9]{32}\.png\z/', $filename);
        self::assertFileExists($this->temporaryDirectory . DIRECTORY_SEPARATOR . 'profil' . DIRECTORY_SEPARATOR . $filename);
    }

    public function testUploadImageRejectsUnsafeDirectories(): void
    {
        $source = $this->createPngUpload('image.png');
        $uploader = new FileUploader($this->temporaryDirectory, new AsciiSlugger());

        $this->expectException(\InvalidArgumentException::class);
        $uploader->uploadImage($source, '..');
    }

    public function testUploadImageRejectsNonImageFiles(): void
    {
        $sourcePath = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'notes.txt';
        file_put_contents($sourcePath, 'not an image');
        $source = new UploadedFile($sourcePath, 'notes.txt', 'text/plain', UPLOAD_ERR_OK, true);
        $uploader = new FileUploader($this->temporaryDirectory, new AsciiSlugger());

        $this->expectException(\InvalidArgumentException::class);
        $uploader->uploadImage($source, 'profil');
    }

    private function createPngUpload(string $filename): UploadedFile
    {
        $path = $this->temporaryDirectory . DIRECTORY_SEPARATOR . bin2hex(random_bytes(6)) . '.png';
        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jq1sAAAAASUVORK5CYII=', true)
        );

        return new UploadedFile($path, $filename, 'image/png', UPLOAD_ERR_OK, true);
    }
}
