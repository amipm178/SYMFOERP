<?php

declare(strict_types=1);

use App\Entity\SalesInvoice;
use App\Entity\SalesOrder;
use App\Entity\SalesPayment;
use App\Entity\SalesQuote;
use App\Entity\SalesQuoteLine;
use App\Entity\ThirdParty;

require dirname(__DIR__).'/vendor/autoload.php';

function expectInvoice(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$customer = new ThirdParty('Client facture test', ThirdParty::CATEGORY_CUSTOMER);
$quote = new SalesQuote($customer);
$quote->addLine(new SalesQuoteLine('Prestation', '2', '50.00', '20.00', 1));
$quote->setStatus(SalesQuote::STATUS_SENT)->setStatus(SalesQuote::STATUS_ACCEPTED);
$order = new SalesOrder($quote);
$invoice = new SalesInvoice($order);

expectInvoice($invoice->getStatus() === SalesInvoice::STATUS_DRAFT, 'New invoices must remain drafts.');
expectInvoice($invoice->getNumber() === null, 'Draft invoices must not receive an official number.');
expectInvoice($invoice->getLines()->count() === 1, 'Invoice must copy order lines.');
expectInvoice($invoice->getTotalExcludingTax() === 100.0, 'Invoice subtotal must match order subtotal.');
expectInvoice($invoice->getTotalTax() === 20.0, 'Invoice VAT must be calculated per line.');
expectInvoice($invoice->getTotalIncludingTax() === 120.0, 'Invoice total must include VAT.');
expectInvoice($invoice->getCustomer() === $customer, 'Invoice must retain the order customer.');
expectInvoice($invoice->getPaidAmount() === 0.0, 'New invoices must have no payments.');
expectInvoice($invoice->getRemainingAmount() === 120.0, 'New invoices must have their full balance outstanding.');

$status = new ReflectionProperty(SalesInvoice::class, 'status');
$status->setValue($invoice, SalesInvoice::STATUS_ISSUED);
$firstPayment = new SalesPayment($invoice, '40.00', new DateTimeImmutable('2026-10-30'), 'bank_transfer', 'VIR-001');
$invoice->addPayment($firstPayment);
expectInvoice($invoice->getStatus() === SalesInvoice::STATUS_PARTIALLY_PAID, 'Partial payment must update invoice status.');
expectInvoice($invoice->getPaidAmount() === 40.0, 'Paid amount must aggregate payments.');
expectInvoice($invoice->getRemainingAmount() === 80.0, 'Remaining balance must decrease after payment.');

$secondPayment = new SalesPayment($invoice, '80.00', new DateTimeImmutable('2026-10-30'), 'card');
$invoice->addPayment($secondPayment);
expectInvoice($invoice->getStatus() === SalesInvoice::STATUS_PAID, 'Full payment must mark invoice as paid.');
expectInvoice($invoice->getRemainingAmount() === 0.0, 'Fully paid invoice must have no remaining balance.');

try {
    $invoice->addPayment(new SalesPayment($invoice, '1.00', new DateTimeImmutable('2026-10-30'), 'cash'));
    throw new RuntimeException('An overpayment was accepted.');
} catch (InvalidArgumentException) {
    // Fully settled invoices reject further payments.
}

$order->setStatus(SalesOrder::STATUS_CANCELLED);
try {
    new SalesInvoice($order);
    throw new RuntimeException('A cancelled order was invoiced.');
} catch (InvalidArgumentException) {
    // Cancelled orders cannot be invoiced.
}

echo "Sales invoice and payment smoke tests passed.\n";
