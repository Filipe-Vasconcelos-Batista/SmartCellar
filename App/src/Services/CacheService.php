<?php

namespace App\Services;

use Symfony\Contracts\Cache\CacheInterface;

class CacheService
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    public function getCachedProductInfo(string $cacheKey):array{
        $items=$this->cache->getItem($cacheKey);
        return $items->isHit()?$items->get():[];
    }
    public function saveProductInfo(string $cacheKey,array $productInfo):void{
        error_log("Saving product info to cache with key: $cacheKey");
        error_log("Product info: " . json_encode($productInfo));
        $items=$this->cache->getItem($cacheKey);
        $items->expiresAfter(3600);
        $items->set($productInfo);
        $this->cache->save($items);
    }
    public function updateProductInfo(string $cacheKey, array $newProductInfo):void
    {
        $existingProductInfo = $this->getCachedProductInfo("storage" . $cacheKey);
        $barcode=$newProductInfo['barcode'];
        $found = false;
        foreach($existingProductInfo as &$product) {
            if (isset($product['barcode']) && $product['barcode'] === $barcode) {
                $product['quantity'] = isset($product['quantity']) ? $product['quantity'] + 1 : 2;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $newProductInfo['quantity'] = 1;
            $existingProductInfo[] = $newProductInfo;
        }
        $newCacheKey='storage' . $cacheKey;
        $this->saveProductInfo($newCacheKey, $existingProductInfo);
    }

    public function clearCache(): void
    {
        $this->cache->clear();
    }
    public function deleteProductInfo(string $cacheKey, string $barcode): void
    {
        $existingProductInfo = $this->getCachedProductInfo("storage". $cacheKey);
        $updatedProductInfo = [];

        foreach ($existingProductInfo as $product) {
            if (isset($product['barcode']) && $product['barcode'] === $barcode) {
                continue;
            }
            $updatedProductInfo[] = $product;
        }

        $this->saveProductInfo($cacheKey, $updatedProductInfo);
    }

}