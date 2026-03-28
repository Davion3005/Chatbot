<?php

namespace App\Service\RAG;

use Illuminate\Http\UploadedFile;
use Spatie\PdfToText\Pdf;

class FileProcessor
{
    public function extractText(UploadedFile $file): string
    {
        $ext = $file->getClientOriginalExtension();

        return match ($ext) {
            'txt' => $this->extractTextFromTxt($file),
            'pdf' => $this->extractTextFromPdf($file),
            'docx' => $this->extractTextFromDocx($file),

            default => throw new \Exception("Unsupported file type: $ext"),
        };
    }

    private function extractTextFromTxt(UploadedFile $file): string
    {
        return file_get_contents($file->getRealPath());
    }

    private function extractTextFromPdf(UploadedFile $file): string
    {
        return Pdf::getText($file->getRealPath());
    }

    private function extractTextFromDocx($file): string
    {
        $zip = new \ZipArchive();
        $zip->open($file->getRealPath());
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        return strip_tags($xml);
    }

}
