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
        $items=$this->cache->getItem($cacheKey);
        $items->expiresAfter(3600);
        $items->set($productInfo);
        $this->cache->save($items);
    }
    public function updateProductInfo(string $cachekey, array $newProductInfo):void
    {
        $existingProductInfo = $this->getCachedProductInfo($cachekey);
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
        $this->saveProductInfo($cachekey, $existingProductInfo);
    }

    public function clearCache(string $cacheKey = null): void
    {
        if ($cacheKey) {
            $this->cache->deleteItem($cacheKey);
        } else {
            $this->cache->clear();
        }
    }
}