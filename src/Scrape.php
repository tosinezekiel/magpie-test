<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{
    private const OUTPUT_FILE_NAME = 'output.json';
    private array $products = [];
    private const BASE_URL = 'https://www.magpiehq.com/developer-challenge/smartphones/';
    
    public function run(): void {
        $pages = $this->getPages();
        $this->crawPages($pages);
        
        $this->saveToJson();
    }

    /**
     * Fetches and returns the available pages for scraping.
     *
     * @return array The list of available pages.
     */
    public function getPages(): array 
    {
        $document = ScrapeHelper::fetchDocument(self::BASE_URL);
        $pages = $document->filter('#pages a');

        return $pages->each(function (Crawler $page, $i) {
            return $page->text();
        });
    }

    /**
     * Crawls the specified pages and processes products.
     *
     * This method fetches and processes products from multiple pages by iterating
     * through the provided list of page numbers and fetching the corresponding URLs.
     *
     * @param array $pages The list of page numbers to crawl.
     * @throws \Exception If there is an issue with fetching or processing a page.
     */
    public function crawPages(array $pages): void {
        try{
            foreach ($pages as $page) {
                $document = ScrapeHelper::fetchDocument(self::BASE_URL . '/?page=' . $page);
                $this->processProducts($document);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Processes product nodes from the given document.
     *
     * @param Crawler $document
     */
    private function processProducts(Crawler $document): void 
    {
        $document->filter('#products .product')->each(function (Crawler $productNode, $i) {
            $productData = $this->extractProductData($productNode->filter('div'));
            $this->handleVariants($productNode, $productData);
        });
    }

    /**
     * Extracts product data from a product node.
     *
     * @param Crawler $productNode.
     * @return array.
     */
    private function extractProductData(Crawler $productNode): array 
    {
        $capacity = $productNode->filter('h3 > .product-capacity')->text();
        $capacityMB = $this->convertCapacityToMB($capacity);

        $title = $productNode->filter('h3 > .product-name')->text() . ' ' . $capacity;
        $imageUrl = $productNode->filter('img')->image()->getUri();
        $price = $this->cleanPrice($productNode->filter('.block.text-lg')->text());
        $availabilityText = $this->cleanAvailabilityText($productNode->filter('.text-center.text-sm')->eq(0)->text());
        $shippingText = $productNode->filter('.text-center.text-sm')->eq(1)->text('null');
        $shippingDate = $this->extractShippingDate($shippingText);
        $isAvailable = strpos($availabilityText, 'In Stock') !== false;

        return compact('title', 'price', 'imageUrl', 'capacityMB', 'availabilityText', 'isAvailable', 'shippingText', 'shippingDate');
    }

    /**
     * Handles product variants and adds unique products to the list.
     *
     * @param Crawler $productNode 
     * @param array $productData
     */
    private function handleVariants(Crawler $productNode, array $productData): void 
    {
        $colorNodes = $productNode->filter('.border.border-black.rounded-full.block');

        $colorNodes->each(function (Crawler $colorNode) use (&$colorVariants, $productData) {
            $color = $colorNode->attr('data-colour');

            $productData['color'] = $color;
            if (!$this->checkIfProductExists($productData)) {
                $product = new Product(
                    $productData['title'], 
                    $productData['price'], 
                    $productData['imageUrl'],
                    $productData['capacityMB'],
                    $color,
                    $productData['availabilityText'],
                    $productData['isAvailable'],
                    $productData['shippingText'],
                    $productData['shippingDate']
                );

                $this->products[] = $product;

            }

        });
    }

    /**
     * Checks if a product with the same title, color, and price already exists in the list.
     *
     * @param array $product
     * @return bool
     */
    private function checkIfProductExists(array $product) : bool
    {

        $foundItems = array_filter($this->products, function ($item) use ($product) {
            return (
                $item->title === $product["title"] &&
                $item->color === $product["color"] &&
                $item->price === $product["price"]
            );
        });

        if(!$foundItems){
            return false;
        }

        return true;
    }

    /**
     * Converts a capacity string to megabytes (MB).
     *
     * @param string $capacity
     * @return int
     */
    private function convertCapacityToMB(string $capacity): int 
    {
        preg_match('/(\d+)\s*([A-Za-z]+)/', $capacity, $matches);

        if (count($matches) === 3) {
            $value = (int)$matches[1];
            $unit = strtolower($matches[2]);

            if ($unit === 'gb') $value *= 1024;

            return $value;
        }

        return 0;
    }

    /**
     * Cleans the availability text by removing prefixes.
     *
     * @param string $text
     * @return string
     */
    private function cleanAvailabilityText(string $text): string 
    {
        return str_replace('Availability: ', '', $text);
    }

    /**
     * Cleans and converts a price string to a float value.
     *
     * @param string $price
     * @return float
     */
    private function cleanPrice(string $price): float 
    {
        return (float) preg_replace('/[^0-9.]/', '', $price);
    }

    /**
     * Extracts a shipping date from a text value.
     *
     * @param string $value
     * @return string|null
     */
    private function extractShippingDate(string $value): string|null 
    {
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $value, $matches)) {
            $extractedDate = $matches[0];
            return $extractedDate;
        }
        
        $pattern = '/(\d{1,2}(st|nd|rd|th)?\s(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s\d{4})/i';
        preg_match($pattern, $value, $matches);

        if (empty($matches)) return null;

        $formattedDate = date('Y-m-d', strtotime($matches[0]));
        return $formattedDate;
    }
    

    /**
     * Saves the list of products to a JSON file.
     *
     */
    private function saveToJson(): void 
    {
        file_put_contents(self::OUTPUT_FILE_NAME, json_encode($this->products, JSON_PRETTY_PRINT));
    }

}

$scrape = new Scrape();
$scrape->run();