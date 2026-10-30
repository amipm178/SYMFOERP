<?php

namespace App\\Controller;

use App\\Entity\\ThirdParty;
use App\\Repository\\ThirdPartyRepository;
use Doctrine\\ORM\\EntityManagerInterface;
use Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController;
use Symfony\\Component\\HttpFoundation\\Request;
use Symfony\\Component\\HttpFoundation\\Response;
use Symfony\\Component\\Routing\\Attribute\\Route;

final class ThirdPartyController extends AbstractController
{
    #[Route('/third-parties', name: 'app_third_parties', methods: ['GET'])]
    public function index(Request $request, ThirdPartyRepository $thirdParties): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $category = (string) $request->query->get('category', '');

        return $this->render('third_party/index.html.twig', [
            'third_parties' => $thirdParties->search($search, $category),
            'search' => $search,
            'selected_category' => $category,
            'categories' => $this->categories(),
        ]);
    }

    #[Route('/third-parties/new', name: 'app_third_party_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $thirdParty = new ThirdParty('');
        $errors = [];

        if ($request->isMethod('POST')) {
            if (!$this->validCsrf($request, 'third_party_create')) {
                throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
            }

            $errors = $this->applyFormData($thirdParty, $request);
            if ($errors === []) {
                $entityManager->persist($thirdParty);
                $entityManager->flush();

                $this->addFlash('success', 'Le tiers a été créé.');

                return $this->redirectToRoute('app_third_parties');
            }
        }

        return $this->render('third_party/form.html.twig', [
            'third_party' => $thirdParty,
            'errors' => $errors,
            'form_token' => $this->csrfToken($request, 'third_party_create'),
            'is_edit' => false,
        ]);
    }

    #[Route('/third-parties/{id}', name: 'app_third_party_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(ThirdParty $thirdParty): Response
    {
        return $this->render('third_party/show.html.twig', ['third_party' => $thirdParty]);
    }

    #[Route('/third-parties/{id}/edit', name: 'app_third_party_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, ThirdParty $thirdParty, EntityManagerInterface $entityManager): Response
    {
        $errors = [];
        if ($request->isMethod('POST')) {
            if (!$this->validCsrf($request, 'third_party_edit_'.$thirdParty->getId())) {
                throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
            }

            $errors = $this->applyFormData($thirdParty, $request);
            if ($errors === []) {
                $entityManager->flush();
                $this->addFlash('success', 'La fiche du tiers a été mise à jour.');

                return $this->redirectToRoute('app_third_party_show', ['id' => $thirdParty->getId()]);
            }
        }

        return $this->render('third_party/form.html.twig', [
            'third_party' => $thirdParty,
            'errors' => $errors,
            'form_token' => $this->csrfToken($request, 'third_party_edit_'.$thirdParty->getId()),
            'is_edit' => true,
        ]);
    }

    #[Route('/third-parties/{id}/delete', name: 'app_third_party_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(Request $request, ThirdParty $thirdParty, EntityManagerInterface $entityManager): Response
    {
        if (!$this->validCsrf($request, 'third_party_delete_'.$thirdParty->getId())) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $entityManager->remove($thirdParty);
        $entityManager->flush();
        $this->addFlash('success', 'Le tiers a été supprimé.');

        return $this->redirectToRoute('app_third_parties');
    }

    /** @return list<string> */
    private function applyFormData(ThirdParty $thirdParty, Request $request): array
    {
        $name = trim((string) $request->request->get('name', ''));
        $category = (string) $request->request->get('category', '');
        $email = trim((string) $request->request->get('email', ''));
        $phone = trim((string) $request->request->get('phone', ''));
        $errors = [];

        if ($name === '' || mb_strlen($name) > 180) {
            $errors[] = 'Le nom est obligatoire et doit contenir au maximum 180 caractères.';
        }
        if (!in_array($category, [ThirdParty::CATEGORY_CUSTOMER, ThirdParty::CATEGORY_PROSPECT, ThirdParty::CATEGORY_SUPPLIER], true)) {
            $errors[] = 'Veuillez sélectionner une catégorie valide.';
        }
        if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180)) {
            $errors[] = 'Veuillez saisir une adresse e-mail valide (180 caractères maximum).';
        }
        if (mb_strlen($phone) > 40) {
            $errors[] = 'Le téléphone ne doit pas dépasser 40 caractères.';
        }

        if ($errors !== []) {
            return $errors;
        }

        $thirdParty->setName($name)
            ->setCategory($category)
            ->setEmail($email)
            ->setPhone($phone);

        return [];
    }

    private function csrfToken(Request $request, string $id): string
    {
        $session = $request->getSession();
        $tokens = $session->get('_third_party_tokens', []);
        if (!isset($tokens[$id])) {
            $tokens[$id] = bin2hex(random_bytes(32));
            $session->set('_third_party_tokens', $tokens);
        }

        return $tokens[$id];
    }

    private function validCsrf(Request $request, string $id): bool
    {
        $tokens = $request->getSession()->get('_third_party_tokens', []);
        $expected = $tokens[$id] ?? '';

        return is_string($expected)
            && $expected !== ''
            && hash_equals($expected, (string) $request->request->get('_token', ''));
    }

    /** @return array<string, string> */
    private function categories(): array
    {
        return [
            ThirdParty::CATEGORY_CUSTOMER => 'Client',
            ThirdParty::CATEGORY_PROSPECT => 'Prospect',
            ThirdParty::CATEGORY_SUPPLIER => 'Fournisseur',
        ];
    }
}
