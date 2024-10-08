<?php

namespace App\Services;

use GuzzleHttp\Client;

class ApiBarcodeScanService
{
    private PhotosService $photosService;
    private string $apiKey;

    public function __construct(PhotosService $photosService)
    {
        $this->apiKey = $_ENV['API_KEY'];
        if (!$this->apiKey) {
            throw new \RuntimeException('API key not set');
        }
        $this->photosService = $photosService;
    }

    public function getCode(string $filepath): string
    {
        $client = new Client();
        if (!file_exists($filepath)) {
            throw new \RuntimeException('File not Found at: '.$filepath);
        }
        try {
            $response = $client->post('https://api.cloudmersive.com/barcode/scan/image', [
                'headers' => [
                    'Apikey' => $this->apiKey,
                ],
                'multipart' => [
                    [
                        'name' => 'imageFile',
                        'contents' => fopen($filepath, 'r'),
                        'filename' => basename($filepath),
                    ],
                ],
            ]);
            $new = json_decode($response->getBody(), true);
            if (empty($new['RawText'])) {
                $this->photosService->deletePhotos($filepath);
                throw new \RuntimeException('No barcode returned');
            }

            return $new['RawText'];
        } catch (\Exception $exception) {
            $this->photosService->deletePhotos($filepath);
            throw new \RuntimeException('Error during the barcode scan: '.$exception->getMessage());
        }
    }
}
