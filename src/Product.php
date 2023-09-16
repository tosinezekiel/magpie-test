<?php

namespace App;

class Product
{
    public string $title;
    public float $price;
    public string $imageUrl;
    public int $capacityMB;
    public string $color;
    public string $availabilityText;
    public bool $isAvailable;
    public ?string $shippingText;
    public ?string $shippingDate;

    public function __construct(
        string $title,
        float $price,
        string $imageUrl,
        int $capacityMB,
        string $color,
        string $availabilityText,
        bool $isAvailable,
        ?string $shippingText,
        ?string $shippingDate
    ) {
        $this->title = $title;
        $this->price = $price;
        $this->imageUrl = $imageUrl;
        $this->capacityMB = $capacityMB;
        $this->color = $color;
        $this->availabilityText = $availabilityText;
        $this->isAvailable = $isAvailable;
        $this->shippingText = $shippingText;
        $this->shippingDate = $shippingDate;
    }
}
