<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    /**
     * Traiter et optimiser un avatar téléchargé
     * @param UploadedFile $file
     * @param string $directory
     * @return string Path du fichier optimisé
     */
    public function processAndStore(UploadedFile $file, string $directory = 'avatars'): string
    {
        $sourcePath = $file->getRealPath();
        $mimeType = $file->getMimeType();

        // Créer l'image à partir du fichier téléchargé
        if ($mimeType === 'image/png') {
            $image = \imagecreatefrompng($sourcePath);
            \imagealphablending($image, false);
            \imagesavealpha($image, true);
        } elseif ($mimeType === 'image/webp') {
            $image = \imagecreatefromwebp($sourcePath);
        } else {
            $image = \imagecreatefromjpeg($sourcePath);
        }

        if (!$image) {
            throw new \Exception('Unable to process image file');
        }

        // Obtenir les dimensions originales
        $width = \imagesx($image);
        $height = \imagesy($image);

        // Redimensionner à 500x500px en préservant le ratio
        $size = min(500, min($width, $height));
        $newImage = \imagecreatetruecolor($size, $size);

        // Pour PNG, préserver la transparence
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            \imagealphablending($newImage, false);
            \imagesavealpha($newImage, true);
        }

        // Calculer les positions pour centrer l'image
        $srcX = (int)(($width - $size) / 2);
        $srcY = (int)(($height - $size) / 2);

        // Copier et redimensionner
        \imagecopyresampled(
            $newImage, $image,
            0, 0,
            $srcX, $srcY,
            $size, $size,
            $size, $size
        );

        // Sauvegarder l'image optimisée
        $filename = uniqid('avatar_') . '.jpg';
        $path = $directory . '/' . $filename;

        // Créer un fichier temporaire pour la sauvegarde optimisée
        $tempPath = storage_path('app/tmp_' . $filename);
        \imagejpeg($newImage, $tempPath, 85);

        // Stocker le fichier
        Storage::disk('public')->put($path, file_get_contents($tempPath));

        // Nettoyer les ressources
        \imagedestroy($image);
        \imagedestroy($newImage);
        @unlink($tempPath);

        return $path;
    }

    /**
     * Générer une image d'avatar par défaut avec initiales
     * @param string $name
     * @param string $size
     * @return string
     */
    public function generateDefaultAvatar(string $name, string $size = '500x500'): string
    {
        $initials = $this->getInitials($name);
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($initials)
            . '&background=0d9488&color=fff&size=' . $size
            . '&bold=true&font-size=0.4&format=webp';
    }

    /**
     * Extraire les initiales d'un nom
     * @param string $name
     * @return string
     */
    private function getInitials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $initials = '';

        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper($part[0]);
            }
        }

        return substr($initials, 0, 2);
    }

    /**
     * Supprimer un avatar existant
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
        } catch (\Exception $e) {
            \Log::error('Avatar deletion error: ' . $e->getMessage());
        }

        return false;
    }
}
