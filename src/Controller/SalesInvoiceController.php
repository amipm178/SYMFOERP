<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SalesInvoice;
use App\Entity\SalesOrder;
use App\Entity\SalesPayment;
use App\Repository\SalesInvoiceRepository;
use DateTimeImmutable;
use InvalidArgumentException;
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

    #[Route('/{id}/payments', name: 'app_sales_invoice_payment', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function addPayment(Request $request, SalesInvoice $invoice, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('sales_invoice_payment_'.$invoice->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        try {
            $amount = trim((string) $request->request->get('amount', ''));
            $date = new DateTimeImmutable((string) $request->request->get('paid_at', ''));
            $method = (string) $request->request->get('method', '');
            $reference = (string) $request->request->get('reference', '');
            $note = (string) $request->request->get('note', '');

            $entityManager->getConnection()->transactional(function () use ($entityManager, $invoice, $amount, $date, $method, $reference, $note): void {
                $entityManager->lock($invoice, LockMode::PESSIMISTIC_WRITE);
                $payment = new SalesPayment($invoice, $amount, $date, $method, $reference, $note);
                $invoice->addPayment($payment);
                $entityManager->persist($payment);
                $entityManager->flush();
            });
            $this->addFlash('success', 'Le règlement a été enregistré.');
        } catch (InvalidArgumentException $exception) {
            $this->addFlash('error', $exception->getMessage());
        } catch (\Throwable) {
            $this->addFlash('error', 'Le règlement n’a pas pu être enregistré. Vérifiez les informations saisies.');
        }

        return $this->redirectToRoute('app_sales_invoice_show', ['id' => $invoice->getId()]);
    }

    #[Route('/{id}', name: 'app_sales_invoice_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(SalesInvoice $invoice): Response
    {
        return $this->render('sales_invoice/show.html.twig', ['invoice' => $invoice]);
    }
}
