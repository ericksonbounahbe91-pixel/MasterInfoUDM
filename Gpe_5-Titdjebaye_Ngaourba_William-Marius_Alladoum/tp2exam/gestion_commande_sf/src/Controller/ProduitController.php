<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Service\PhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produits')]
final class ProduitController extends AbstractController
{
    #[Route('', name: 'app_produit_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $repo): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        return $this->render('produit/index.html.twig', ['produits' => $repo->recherche($q), 'q' => $q]);
    }

    #[Route('/nouveau', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, PhotoUploader $uploader): Response
    {
        return $this->traiter(new Produit(), $request, $em, $uploader, 'Nouveau produit');
    }

    #[Route('/{id}/modifier', name: 'app_produit_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Produit $produit, Request $request, EntityManagerInterface $em, PhotoUploader $uploader): Response
    {
        return $this->traiter($produit, $request, $em, $uploader, 'Modifier le produit');
    }

    /** Logique commune à la création et à la modification. */
    private function traiter(Produit $produit, Request $request, EntityManagerInterface $em, PhotoUploader $uploader, string $titre): Response
    {
        $estNouveau = $produit->getId() === null;
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $form->get('photoFichier')->getData();
            if ($fichier) {
                $produit->setPhoto($uploader->enregistrer($fichier));
            }
            $em->persist($produit);   // INSERT si nouveau, UPDATE sinon : Doctrine décide
            $em->flush();
            $this->addFlash('success', $estNouveau ? 'Produit ajouté.' : 'Produit modifié.');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('produit/form.html.twig', ['form' => $form, 'produit' => $produit, 'titre' => $titre]);
    }
}
