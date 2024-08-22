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

    private function getPrefixedCacheKey(string $cacheKey): string
    {
        return 'storage'.$cacheKey;
    }

    public function getCachedProductInfo(string $cacheKey): array
    {
        $items = $this->cache->getItem($cacheKey);

        return $items->isHit() ? $items->get() : [];
    }

    public function saveProductInfo(string $cacheKey, array $productInfo): void
    {
        $items = $this->cache->getItem($cacheKey);
        $items->expiresAfter(3600);
        $items->set($productInfo);
        $this->cache->save($items);
    }

    public function updateProductQuantity(string $cacheKey, array $newProductInfo): void
    {
        $existingProductInfo = $this->getCachedProductInfo($this->getPrefixedCacheKey($cacheKey));
        $barcode = $newProductInfo['barcode'];
        $found = false;
        foreach ($existingProductInfo as &$product) {
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
        $this->saveProductInfo($this->getPrefixedCacheKey($cacheKey), $existingProductInfo);
    }

    public function updateProductInfo(string $cacheKey, array $newProductInfo): void
    {
        $existingProductInfo = $this->getCachedProductInfo($this->getPrefixedCacheKey($cacheKey));
        $barcode = $newProductInfo['barcode'];
        $updatedProductInfo = [];
        foreach ($existingProductInfo as &$product) {
            if (isset($product['barcode']) && $product['barcode'] === $barcode) {
                continue;
            }
            $updatedProductInfo[] = $product;
        }
        $updatedProductInfo[] = [
            'id' => $newProductInfo['id'],
            'quantity' => $newProductInfo['quantity'],
            'barcode' => $newProductInfo['barcode'],
            'title' => $newProductInfo['title'],
            'category' => $newProductInfo['category'],
        ];
        $this->saveProductInfo($this->getPrefixedCacheKey($cacheKey), $updatedProductInfo);
    }

    public function deleteInfo(string $cacheKey): void
    {
        $this->cache->delete($cacheKey);
    }

    public function clearCache(): void
    {
        $this->cache->clear();
    }

    public function deleteProductInfo(string $cacheKey, string $barcode): void
    {
        $existingProductInfo = $this->getCachedProductInfo($this->getPrefixedCacheKey($cacheKey));
        $updatedProductInfo = [];

        foreach ($existingProductInfo as $product) {
            if (isset($product['barcode']) && $product['barcode'] === $barcode) {
                continue;
            }
            $updatedProductInfo[] = $product;
        }

        $this->saveProductInfo($this->getPrefixedCacheKey($cacheKey), $updatedProductInfo);
    }
}
