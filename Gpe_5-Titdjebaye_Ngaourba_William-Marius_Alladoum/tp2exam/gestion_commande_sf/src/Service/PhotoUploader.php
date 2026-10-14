<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/** Enregistre une photo téléversée dans public/uploads avec un nom aléatoire. */
class PhotoUploader
{
    public function __construct(#[Autowire('%uploads_dir%')] private string $dossier)
    {
    }

    public function enregistrer(UploadedFile $fichier): string
    {
        // L'extension est devinée à partir du CONTENU du fichier (pas du nom envoyé par l'utilisateur).
        $nom = bin2hex(random_bytes(8)) . '.' . ($fichier->guessExtension() ?: 'jpg');
        $fichier->move($this->dossier, $nom);
        return $nom;
    }
}
