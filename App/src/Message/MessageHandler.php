<?php

namespace App\Message;
use App\Services\BarcodeScanService;
use App\Services\ProductLookupService;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;


#[AsMessageHandler]
class MessageHandler
{
    private $barcodeScanService;
    private $productLookUpService;
    private $requestStack;
    public function __construct(BarcodeScanService $barcodeScan, ProductLookupService $productLookupService, RequestStack $requestStack){
        $this->barcodeScanService=$barcodeScan;
        $this->productLookUpService=$productLookupService;
        $this->requestStack = $requestStack;    }
    public function __invoke(UploadPhotoMessage $message, ){
        error_log("Handler invoked");
        $filepath=$message->getFilepath();
        $barcode=$this->barcodeScanService->getCode($filepath);
        if($barcode){
            $newProductInfo=$this->productLookUpService->getProduct($barcode);
            $request=$this->requestStack->getCurrentRequest();
            $response=new Response();
            $existingProductInfo=[];
            if ($request->cookies->has('productInfo')) {
                $existingProductInfo = json_decode($request->cookies->get('productInfo'), true);
            }
            $existingProductInfo[] = $newProductInfo;
            $response->headers->setCookie(new Cookie('productInfo', json_encode($existingProductInfo), time() + (3600 * 24 * 30))); // Cookie expires in 30 days
            $response->send();

        }
    }

}