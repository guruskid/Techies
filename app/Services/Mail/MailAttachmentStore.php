<?php

namespace App\Services\Mail;

use App\Models\EmailAttachment;
use App\Models\EmailMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MailAttachmentStore
{
    public function storeUploaded(EmailMessage $message, UploadedFile $file): EmailAttachment
    {
        return $this->storeContent(
            $message,
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            (string) file_get_contents($file->getRealPath()),
        );
    }

    public function storeContent(EmailMessage $message, string $filename, ?string $mimeType, string $content): EmailAttachment
    {
        $filename = $this->safeName($filename);
        $path = 'mail/attachments/'.Str::uuid()->toString().'-'.$filename;

        Storage::disk('local')->put($path, $content);

        return $message->attachments()->create([
            'filename' => $filename,
            'mime_type' => $mimeType ?: 'application/octet-stream',
            'size' => strlen($content),
            'disk' => 'local',
            'path' => $path,
        ]);
    }

    private function safeName(string $filename): string
    {
        $filename = basename(str_replace('\\', '/', $filename));
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'attachment';

        return Str::limit($filename, 180, '');
    }
}
