<?php

namespace App\Controller;

use App\Dto\AjoutLignes;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Form\AjoutLignesType;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Service\CommandeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commandes')]
final class CommandeController extends AbstractController
{
    /** Liste des commandes + formulaire « nouvelle commande ». */
    #[Route('', name: 'app_commande_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, CommandeRepository $repo): Response
    {
        $commande = (new Commande())->setDateCommande(new \DateTimeImmutable('today'));
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($commande);
            $em->flush();
            $this->addFlash('success', 'Commande créée. Ajoutez maintenant ses produits.');
            return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
        }

        return $this->render('commande/index.html.twig', [
            'commandes' => $repo->listeAvecTotaux(),
            'form' => $form,
        ]);
    }

    /** Détail d'une commande + formulaire d'enregistrement de ses produits. */
    #[Route('/{id}', name: 'app_commande_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(Commande $commande, Request $request, CommandeService $service): Response
    {
        $saisie = new AjoutLignes();
        $form = $this->createForm(AjoutLignesType::class, $saisie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $nb = $service->ajouterLignes($commande, $saisie->lignes);
                $this->addFlash('success', $nb . ' produit(s) enregistré(s) dans la commande. Le stock a été mis à jour.');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    /** Suppression d'un produit dans une commande (POST + jeton CSRF). */
    #[Route('/{id}/lignes/{ligne}/supprimer', name: 'app_commande_ligne_supprimer', requirements: ['id' => '\d+', 'ligne' => '\d+'], methods: ['POST'])]
    public function supprimerLigne(
        Commande $commande,
        #[MapEntity(id: 'ligne')] LigneCommande $ligne,
        Request $request,
        CommandeService $service,
    ): Response {
        if ($ligne->getCommande()?->getId() !== $commande->getId()) {
            throw $this->createNotFoundException();
        }
        if (!$this->isCsrfTokenValid('supprimer_ligne_' . $ligne->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide. Rechargez la page puis recommencez.');
            return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
        }

        $service->retirerLigne($ligne);
        $this->addFlash('success', 'Produit retiré de la commande. Le stock a été rétabli.');
        return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
    }
}
