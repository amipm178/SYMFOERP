<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SalesOrder;
use App\Repository\SalesOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sales/orders')]
final class SalesOrderController extends AbstractController
{
    #[Route('', name: 'app_sales_orders', methods: ['GET'])]
    public function index(SalesOrderRepository $orders): Response
    {
        return $this->render('sales_order/index.html.twig', [
            'orders' => $orders->findBy([], ['createdAt' => 'DESC'], 100),
        ]);
    }

    #[Route('/{id}', name: 'app_sales_order_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(SalesOrder $order): Response
    {
        return $this->render('sales_order/show.html.twig', ['order' => $order]);
    }
}
