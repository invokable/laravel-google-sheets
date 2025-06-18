<?php

namespace Revolution\Google\Sheets\Concerns;

use Google\Service\Drive;
use Revolution\Google\Client\Facades\Google;

trait SheetsDrive
{
    protected ?Drive $drive = null;

    public function spreadsheetList(): array
    {
        $list = [];

        $files = $this->getDriveService()
            ->files
            ->listFiles(
                [
                    'q' => "mimeType = 'application/vnd.google-apps.spreadsheet'",
                ],
            )
            ->getFiles();

        foreach ($files as $file) {
            $list[$file->id] = $file->name;
        }

        return $list;
    }

    public function setDriveService(mixed $drive): self
    {
        $this->drive = $drive;

        return $this;
    }

    public function getDriveService(): Drive
    {
        return $this->drive ??= Google::make('drive');
    }
}
