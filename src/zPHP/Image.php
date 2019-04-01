<?php

/**
 * Author: Zbigniew 'zibi' Jarosik <zibi@nora.pl>
 */

use JupyterPHP\zPHP;

class Image
{
    /** @var JupyterPHP\zPHP */
    private $zPHP;

    public function __construct(zPHP $zPHP)
    {
        $this->zPHP = $zPHP;
    }

    public function __invoke($imageData)
    {
        $image = imagecreatefromstring($imageData);

        $width = imagesx($image);
        $height = imagesy($image);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();

        $this->zPHP->send(
            'display_data',
            [
                'data'=>[
                    'image/png'=>base64_encode($imageData)
                ],
                'metadata'=>[
                    'image/png'=>[
                        'width'=>$width,
                        'height'=>$height
                    ]
                ]
            ]
        );
    }
}
