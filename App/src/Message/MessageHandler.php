<?php

namespace App\Message;
use App\Services\BarcodeScanService;
use App\Services\ProductLookupService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\Handler\AsMessageHandler;

#[AsMessageHandler]
class MessageHandler
{
    private $barcodeScanService;
    private $productLookUpService;
    private $session;
    public function __construct(BarcodeScanService $barcodeScan, ProductLookupService $productLookupService, SessionInterface $session){
        $this->barcodeScanService=$barcodeScan;
        $this->productLookUpService=$productLookupService;
        $this->session=$session;
    }
    public function __invoke(UploadPhotoMessage $message, ){
        $filepath=$message->getFilepath();
        $barcode=$this->barcodeScanService->getCode($filepath);
        if($barcode){
            $newProductInfo=$this->productLookUpService->getProduct($barcode);
            if($this->session->has('productInfo')){
                $existingProductInfo=$this->session->get('productInfo');
                $existingProductInfo[]=$newProductInfo;
                $this->session->set('productInfo',$existingProductInfo);
            }else{
                $this->session->set('productInfo',[$newProductInfo]);

            }

        }
    }

}