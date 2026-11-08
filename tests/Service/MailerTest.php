<?php

namespace App\Tests\Service;

use App\Service\Mailer as AppMailer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerTest extends TestCase
{
    private $temporaryDirectory;

    protected function setUp(): void
    {
        $this->temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'quizz-mailer-' . bin2hex(random_bytes(8));
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

    public function testSendEmailWithAttachmentBuildsAndSendsTheExpectedEmail(): void
    {
        $pdfPath = $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'profil.pdf';
        file_put_contents($pdfPath, "%PDF-1.4\n%%EOF");

        $transport = $this->createMock(MailerInterface::class);
        $sentMessage = null;
        $transport->expects(self::once())
            ->method('send')
            ->willReturnCallback(function ($message) use (&$sentMessage): void {
                $sentMessage = $message;
            });

        $mailer = new AppMailer($transport, 'no-reply@example.test');
        $mailer->sendEmailWithAttachment('reader@example.test', $pdfPath);

        self::assertInstanceOf(Email::class, $sentMessage);
        self::assertSame('no-reply@example.test', $sentMessage->getFrom()[0]->getAddress());
        self::assertSame('reader@example.test', $sentMessage->getTo()[0]->getAddress());
        self::assertStringContainsString('participation', $sentMessage->getTextBody());
        self::assertCount(1, $sentMessage->getAttachments());
    }

    public function testSendEmailWithAttachmentRejectsInvalidRecipient(): void
    {
        $transport = $this->createMock(MailerInterface::class);
        $transport->expects(self::never())->method('send');

        $mailer = new AppMailer($transport, 'no-reply@example.test');

        $this->expectException(\InvalidArgumentException::class);
        $mailer->sendEmailWithAttachment('not-an-email', __FILE__);
    }

    public function testSendEmailWithAttachmentRejectsMissingPdf(): void
    {
        $transport = $this->createMock(MailerInterface::class);
        $transport->expects(self::never())->method('send');

        $mailer = new AppMailer($transport, 'no-reply@example.test');

        $this->expectException(\RuntimeException::class);
        $mailer->sendEmailWithAttachment('reader@example.test', $this->temporaryDirectory . DIRECTORY_SEPARATOR . 'missing.pdf');
    }

    public function testGeneratePdfContentWritesTheGeneratedPdf(): void
    {
        $mailer = new AppMailer($this->createMock(MailerInterface::class), 'no-reply@example.test');

        $path = $mailer->generatePDFContent('<h1>Quiz result</h1>', $this->temporaryDirectory, 'result.pdf');

        self::assertFileExists($path);
        self::assertStringStartsWith('%PDF-', file_get_contents($path));
    }
}
