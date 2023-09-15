<?php

namespace App;

class Product
{
    private string $title;
    private float $price;
    private string $imageUrl;
    private int $capacityMB;
    private string $colour;
    private string $availabilityText;
    private bool $isAvailable;
    private string $shippingText;
    private string $shippingDate;

    public function __construct(
        string $title,
        float $price,
        string $imageUrl,
        int $capacityMB,
        string $colour,
        string $availabilityText,
        bool $isAvailable,
        string $shippingText,
        string $shippingDate
    ) {
        $this->title = $title;
        $this->price = $price;
        $this->imageUrl = $imageUrl;
        $this->capacityMB = $capacityMB;
        $this->colour = $colour;
        $this->availabilityText = $availabilityText;
        $this->isAvailable = $isAvailable;
        $this->shippingText = $shippingText;
        $this->shippingDate = $shippingDate;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
}
