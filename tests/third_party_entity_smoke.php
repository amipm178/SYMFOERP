<?php

declare(strict_types=1);

use App\Entity\ThirdParty;

require dirname(__DIR__).'/vendor/autoload.php';

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$thirdParty = new ThirdParty('  Société Exemple  ', ThirdParty::CATEGORY_CUSTOMER);
expect($thirdParty->getName() === 'Société Exemple', 'The name must be trimmed.');
expect($thirdParty->getCategory() === ThirdParty::CATEGORY_CUSTOMER, 'The customer category must be preserved.');
expect($thirdParty->getCreatedAt() instanceof DateTimeImmutable, 'Creation date must be immutable.');

$thirdParty->setEmail('  contact@example.test  ');
$thirdParty->setPhone('  0102030405  ');
expect($thirdParty->getEmail() === 'contact@example.test', 'The email must be trimmed.');
expect($thirdParty->getPhone() === '0102030405', 'The phone must be trimmed.');

$thirdParty->setEmail(' ');
$thirdParty->setPhone('');
expect($thirdParty->getEmail() === null, 'An empty email must be normalized to null.');
expect($thirdParty->getPhone() === null, 'An empty phone must be normalized to null.');

foreach (['', '   '] as $invalidName) {
    try {
        $thirdParty->setName($invalidName);
        throw new RuntimeException('An empty name was accepted.');
    } catch (InvalidArgumentException) {
        // Expected validation failure.
    }
}

try {
    $thirdParty->setCategory('unknown');
    throw new RuntimeException('An unsupported category was accepted.');
} catch (InvalidArgumentException) {
    // Expected validation failure.
}

echo "Third-party entity smoke tests passed.\n";
