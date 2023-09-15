<?php

namespace App;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{
    private const OUTPUT_FILE_NAME = 'output.json';
    private const BASE_URL = 'https://www.magpiehq.com';
    private array $products = [];

    public function run(): void
    {
        // try {
            $pages = $this->getPages();
            $this->crawPages($pages);

            $this->saveToJson();
        // } catch (\Exception $e) {
        //     echo "Error: " . $e->getMessage();
        // }
    }

    public function getPages(): array
    {
        $url = $this->setUrl('developer-challenge/smartphones');
        $document = ScrapeHelper::fetchDocument($url);

        $pages = $document->filter('#pages a');

        return $pages->each(function (Crawler $page, $i) {
            return $page->text();
        });
    }

    
    public function crawPages(array $pages): void
    {
        foreach ($pages as $page) {
            $document = ScrapeHelper::fetchDocument(self::BASE_URL . '/?page=' . $page);
            
            $this->processProducts($document);
        }
    }

    private function processProducts(Crawler $document): void
    {
        $productNodes = $document->filter('.product');

        $productNodes->each(function (Crawler $productNode) {
            $productData = $this->extractProductData($productNode);
            $this->handleVariants($productNode, $productData);
        });
    }

    private function extractProductData(Crawler $productNode): array
    {
        $title = $productNode->filter('.product-name')->text();
        $imageUrl = $productNode->filter('img')->attr('src');
        $priceText = trim($productNode->filter('.block.text-lg')->text());
        $availabilityText = trim($productNode->filter('.text-center.text-sm')->eq(0)->text());
        $shippingText = trim($productNode->filter('.text-center.text-sm')->eq(1)->text());

        $capacityText = $productNode->filter('.product-capacity')->text();
        $capacityMB = $this->convertCapacityToMB($capacityText);

        return compact('title', 'imageUrl', 'priceText', 'availabilityText', 'shippingText', 'capacityMB');
    }

    private function handleVariants(Crawler $productNode, array $productData): void
    {
        $colorNodes = $productNode->filter('.border.border-black.rounded-full.block');

        $colorVariants = [];
        $colorNodes->each(function (Crawler $colorNode) use (&$colorVariants, $productData) {
            $color = $colorNode->attr('data-colour');
            $productVariantData = $productData + ['color' => $color];

            if (!$this->isProductDuplicate($productVariantData)) {
                $colorVariants[] = $productVariantData;
            }
        });

        $this->products = array_merge($this->products, $colorVariants);
    }

    private function setUrl(string $path){
        return self::BASE_URL . '/' . $path;
    }

    private function convertCapacityToMB(string $capacityText): int
    {
        preg_match('/(\d+)\s*([A-Za-z]+)/', $capacityText, $matches);

        if (count($matches) === 3) {
            $value = (int)$matches[1];
            $unit = strtolower($matches[2]);

            if ($unit === 'gb') {
                $value *= 1024;
            }

            return $value;
        }

        return 0; 
    }

    private function isProductDuplicate(array $productVariantData): bool
    {
        foreach ($this->products as $existingProduct) {
            if (
                $existingProduct['title'] === $productVariantData['title'] &&
                $existingProduct['priceText'] === $productVariantData['priceText'] &&
                $existingProduct['imageUrl'] === $productVariantData['imageUrl'] &&
                $existingProduct['capacityMB'] === $productVariantData['capacityMB'] &&
                $existingProduct['color'] === $productVariantData['color'] &&
                $existingProduct['availabilityText'] === $productVariantData['availabilityText'] &&
                $existingProduct['shippingText'] === $productVariantData['shippingText']
            ) {
                return true; 
            }
        }

        return false; 
    }


    private function saveToJson(): void
    {
        file_put_contents(self::OUTPUT_FILE_NAME, json_encode($this->products));
    }
}

$scrape = new Scrape();
$scrape->run();
