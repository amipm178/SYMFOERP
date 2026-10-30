<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SalesInvoice;
use App\Entity\SalesOrder;
use App\Repository\SalesInvoiceRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\LockMode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sales/invoices')]
final class SalesInvoiceController extends AbstractController
{
    #[Route('', name: 'app_sales_invoices', methods: ['GET'])]
    public function index(SalesInvoiceRepository $invoices): Response
    {
        return $this->render('sales_invoice/index.html.twig', [
            'invoices' => $invoices->findBy([], ['createdAt' => 'DESC'], 100),
        ]);
    }

    #[Route('/from-order/{id}', name: 'app_sales_invoice_from_order', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function createFromOrder(Request $request, SalesOrder $order, SalesInvoiceRepository $invoices, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('sales_invoice_create_'.$order->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $invoice = $entityManager->getConnection()->transactional(function () use ($entityManager, $invoices, $order): SalesInvoice {
            $entityManager->lock($order, LockMode::PESSIMISTIC_WRITE);
            $existing = $invoices->findOneBy(['order' => $order]);
            if ($existing instanceof SalesInvoice) {
                return $existing;
            }
            $invoice = new SalesInvoice($order);
            $entityManager->persist($invoice);
            $entityManager->flush();
            return $invoice;
        });

        $this->addFlash('success', 'La facture brouillon a été créée. Vérifiez les informations légales avant toute émission.');
        return $this->redirectToRoute('app_sales_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}', name: 'app_sales_invoice_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(SalesInvoice $invoice): Response
    {
        return $this->render('sales_invoice/show.html.twig', ['invoice' => $invoice]);
    }
}
