<?php

declare(strict_types=1);

use App\Entity\SalesQuote;
use App\Entity\SalesQuoteLine;
use App\Entity\SalesOrder;
use App\Entity\ThirdParty;

require dirname(__DIR__).'/vendor/autoload.php';

function expectSales(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$customer = new ThirdParty('Client de test', ThirdParty::CATEGORY_CUSTOMER);
$quote = new SalesQuote($customer);
$quote->addLine(new SalesQuoteLine('Prestation de conseil', '2', '50.00', '20.00', 1));
$quote->addLine(new SalesQuoteLine('Frais de dossier', '1', '10.00', '0.00', 2));

expectSales($quote->getStatus() === SalesQuote::STATUS_DRAFT, 'New quotes must start as drafts.');
expectSales($quote->getLines()->count() === 2, 'Quote lines must be attached to their quote.');
expectSales($quote->getTotalExcludingTax() === 110.0, 'The quote subtotal must be calculated correctly.');
expectSales($quote->getTotalTax() === 20.0, 'VAT must be rounded and summed per line.');
expectSales($quote->getTotalIncludingTax() === 130.0, 'The total including VAT must be calculated correctly.');

$quote->setStatus(SalesQuote::STATUS_SENT);
expectSales($quote->getStatus() === SalesQuote::STATUS_SENT, 'Quote status must be mutable to a supported state.');
$quote->setStatus(SalesQuote::STATUS_ACCEPTED);
$order = new SalesOrder($quote);
expectSales($order->getLines()->count() === 2, 'Order conversion must copy all quote lines.');
expectSales($order->getTotalExcludingTax() === $quote->getTotalExcludingTax(), 'Converted order subtotal must match the accepted quote.');
expectSales($order->getTotalTax() === $quote->getTotalTax(), 'Converted order VAT must match the accepted quote.');
expectSales($order->getTotalIncludingTax() === $quote->getTotalIncludingTax(), 'Converted order total must match the accepted quote.');
try {
    new SalesOrder(new SalesQuote($customer));
    throw new RuntimeException('A draft quote was converted into an order.');
} catch (InvalidArgumentException) {
    // Only accepted quotes may be converted.
}

try {
    $quote->setStatus('unknown');
    throw new RuntimeException('An unsupported quote status was accepted.');
} catch (InvalidArgumentException) {
    // Expected validation failure.
}

try {
    new SalesQuote(new ThirdParty('Prospect de test', ThirdParty::CATEGORY_PROSPECT));
    throw new RuntimeException('A prospect was accepted as a quote customer.');
} catch (InvalidArgumentException) {
    // Only customer records may be quoted.
}

foreach ([
    fn () => new SalesQuoteLine('', '1', '10.00', '20.00', 1),
    fn () => new SalesQuoteLine('Quantité invalide', '0', '10.00', '20.00', 1),
    fn () => new SalesQuoteLine('TVA invalide', '1', '10.00', '120.00', 1),
] as $invalidLine) {
    try {
        $invalidLine();
        throw new RuntimeException('An invalid quote line was accepted.');
    } catch (InvalidArgumentException) {
        // Expected validation failure.
    }
}

echo "Sales quote entity and totals smoke tests passed.\n";
