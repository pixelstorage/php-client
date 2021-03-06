<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2017 César D. Rodas                                               |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/

use PixelStorage\Image;
use PixelStorage\Client;

/**
 * Pixel Storage Client
 *
 * The client should be configured before any usage.
 *
 * We expose static functions to make easier to use after configuration.
 *
 *
 */
class PixelStorage
{
    protected static $client;

    /**
     * Configure
     *
     * Configures the PixelStorage object. 
     */
    public static function configure(Client $client)
    {
        self::$client = $client;
    }

    public static function img($image)
    {
        return self::image($image);
    }

    public static function image($image)
    {
        if (strpos($image, ":") === -1) {
            throw new RuntimeException("Invalid image ID");
        }
        list($image_id, $image_secret) = explode(":", $image, 2);
        return new Image($image_id, $image_secret, self::$client);
    }

    public static function upload($image)
    {
        if (!is_readable($image)) {
            throw new RuntimeException("$image is not readable");
        }

        $upload = self::$client->prepare(['redirect' => '']);

        $response = self::$client->getApiClient()->request('POST', $upload->url, [
            'multipart' => [[
                'name' => 'image',
                'contents' => fopen($image, 'rb'),
            ]],
        ]);

        $body = json_decode($response->getBody());
        if ($body->success && $body->image) {
            return $body->image;
        }

        throw new RuntimeException("Invalid response from the server");
    }
}

if (!is_callable('pixelstorage')) {
    function pixelstorage($url)
    {
        return PixelStorage::image($url);
    }
}
