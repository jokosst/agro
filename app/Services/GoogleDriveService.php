<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoogleDriveService
{
    protected ?Client $client = null;

    protected ?Drive $driveService = null;

    protected ?string $folderId = null;

    public function __construct()
    {
        $clientId = config('services.google_drive.client_id', env('GOOGLE_DRIVE_CLIENT_ID'));
        $clientSecret = config('services.google_drive.client_secret', env('GOOGLE_DRIVE_CLIENT_SECRET'));
        $refreshToken = config('services.google_drive.refresh_token', env('GOOGLE_DRIVE_REFRESH_TOKEN'));
        $this->folderId = config('services.google_drive.folder_id', env('GOOGLE_DRIVE_FOLDER_ID'));

        $serviceAccountPath = env('GOOGLE_DRIVE_SERVICE_ACCOUNT_PATH', storage_path('app/google-drive.json'));

        if (file_exists($serviceAccountPath)) {
            try {
                $this->client = new Client;
                $this->client->setAuthConfig($serviceAccountPath);
                $this->client->addScope(Drive::DRIVE);
                $this->driveService = new Drive($this->client);
            } catch (\Exception $e) {
                Log::warning('Google Drive Service Account initialization failed: '.$e->getMessage());
                $this->driveService = null;
            }
        } elseif ($clientId && $clientSecret && $refreshToken) {
            try {
                $this->client = new Client;
                $this->client->setClientId($clientId);
                $this->client->setClientSecret($clientSecret);
                $this->client->refreshToken($refreshToken);
                $this->driveService = new Drive($this->client);
            } catch (\Exception $e) {
                Log::warning('Google Drive Client initialization failed: '.$e->getMessage());
                $this->driveService = null;
            }
        }
    }

    /**
     * Upload a file either to Google Drive or fallback to local storage
     *
     * @param  UploadedFile|string  $file
     * @return array [ 'url' => string, 'file_id' => string|null, 'storage' => 'google_drive'|'local' ]
     */
    public function uploadFile($file, string $folderName = 'agrocom'): array
    {
        // If Google Drive service is available and configured
        if ($this->driveService !== null) {
            try {
                return $this->uploadToGoogleDrive($file, $folderName);
            } catch (\Exception $e) {
                Log::error('Google Drive upload error: '.$e->getMessage().'. Falling back to local storage.');
            }
        }

        // Fallback to local public storage
        return $this->uploadToLocalStorage($file, $folderName);
    }

    /**
     * Upload to Google Drive directly
     */
    protected function uploadToGoogleDrive($file, string $folderName): array
    {
        $fileName = 'agrocom_'.Str::random(10).'_'.time();
        $mimeType = 'image/jpeg';
        $content = '';

        if ($file instanceof UploadedFile) {
            $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'_'.time().'.'.$file->getClientOriginalExtension();
            $mimeType = $file->getMimeType() ?: 'image/jpeg';
            $content = file_get_contents($file->getRealPath());
        } elseif (is_string($file)) {
            // Check if base64
            if (str_contains($file, ';base64,')) {
                $parts = explode(';base64,', $file);
                $content = base64_decode($parts[1]);
                $fileName .= '.jpg';
            } else {
                $content = $file;
                $fileName .= '.jpg';
            }
        }

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => $this->folderId ? [$this->folderId] : [],
        ]);

        $createdFile = $this->driveService->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink, webContentLink, thumbnailLink',
        ]);

        $fileId = $createdFile->getId();

        // Make file public read so mobile and web can display it
        try {
            $permission = new Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);
            $this->driveService->permissions->create($fileId, $permission);
        } catch (\Exception $e) {
            Log::warning('Google Drive permission setting warning: '.$e->getMessage());
        }

        // Direct image viewable link for Google Drive
        $publicUrl = "https://lh3.googleusercontent.com/u/0/d/{$fileId}";

        return [
            'url' => $publicUrl,
            'file_id' => $fileId,
            'web_view_link' => $createdFile->getWebViewLink(),
            'storage' => 'google_drive',
            'filename' => $fileName,
        ];
    }

    /**
     * Local storage fallback
     */
    protected function uploadToLocalStorage($file, string $folderName): array
    {
        $fileName = 'img_'.Str::random(12).'_'.time().'.jpg';

        if ($file instanceof UploadedFile) {
            $fileName = Str::random(12).'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs("uploads/{$folderName}", $fileName, 'public');
        } elseif (is_string($file)) {
            if (str_contains($file, ';base64,')) {
                $parts = explode(';base64,', $file);
                $data = base64_decode($parts[1]);
            } else {
                $data = $file;
            }
            Storage::disk('public')->put("uploads/{$folderName}/{$fileName}", $data);
            $path = "uploads/{$folderName}/{$fileName}";
        } else {
            throw new \InvalidArgumentException('Unsupported file type for upload.');
        }

        $url = asset("storage/{$path}");

        return [
            'url' => $url,
            'file_id' => $fileName,
            'web_view_link' => $url,
            'storage' => 'local',
            'filename' => $fileName,
        ];
    }
}
