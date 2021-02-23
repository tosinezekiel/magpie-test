<?php

namespace App;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    public function run(): void
    {
        $document = ScrapeHelper::fetchDocument('https://www.magpiehq.com/developer-challenge/smartphones');

        file_put_contents('output.json', json_encode($this->products));
    }
}

$scrape = new Scrape();
$scrape->run();
