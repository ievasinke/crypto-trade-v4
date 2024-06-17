<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class CurrencyServices
{
    public static function displayList(array $cryptoCurrencies): void
    {
        $outputCrypto = new ConsoleOutput();
        $tableCryptoCurrencies = new Table($outputCrypto);
        $tableCryptoCurrencies
            ->setHeaders(['Index', 'Name', 'Symbol', 'Price']);
        $tableCryptoCurrencies
            ->setRows(array_map(function (int $index, Currency $cryptoCurrency): array {
                return [
                    $index + 1,
                    $cryptoCurrency->getName(),
                    $cryptoCurrency->getSymbol(),
                    new TableCell(
                        number_format($cryptoCurrency->getPrice(), 2),
                        ['style' => new TableCellStyle(['align' => 'right',])]
                    ),
                ];
            }, array_keys($cryptoCurrencies), $cryptoCurrencies));
        $tableCryptoCurrencies->setStyle('box-double');
        $tableCryptoCurrencies->render();
    }
}