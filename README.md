## Magpie PHP Developer Challenge

Your task is to gather data about the products listed on https://www.magpiehq.com/developer-challenge/smartphones

The final output of your script should be an array of objects similar to the example below:

```
{
    "title": "iPhone 11 Pro 64GB",
    "price": 123.45,
    "imageUrl": "https://example.com/image.png",
    "capacityMB": 64000,
    "colour": "red",
    "availabilityText": "In Stock",
    "isAvailable": true,
    "shippingText": "Delivered from 25th March",
    "shippingDate": "2021-03-25"
}

```

You should use this repository as a starter template.

You can refer to the [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html) documentation for a nice way to capture the data you need.

Hint: the `Symfony\Component\DomCrawler\Crawler` class,  and its `filter()` method, are your friends.

You can share your code with us by email, or through a service like GitHub.

Please also send us your `output.json`.

### Notes
* Please de-dupe your data. We don’t want to see the same product twice, even if it’s listed twice on the website.
* Make sure that all product variants are captured. Each colour variant should be treated as a separate product.
* Device capacity should be captured in MB for all products (not GB)
* The final output should be an array of products, outputted to output.json
* Don’t forget the pagination!
* You will be assessed both on successfully generating the correct output data in output.json, and also on the quality of your code.

### Useful Resources
* https://symfony.com/doc/current/components/dom_crawler.html
* https://symfony.com/doc/current/components/css_selector.html
* https://github.com/jupeter/clean-code-php

### Requirements

* PHP 7.4+
* Composer

### Setup

```
git clone git@github.com:stickeeuk/magpie-scrape-challenge.git
cd magpie-scrape-challenge
composer install
```

To run the scrape you can use `php src/Scrape.php`
