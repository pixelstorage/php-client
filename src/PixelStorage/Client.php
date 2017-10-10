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
namespace PixelStorage;

use RuntimeException;
use GuzzleHttp;

/**
 * PixelStorage Client
 *
 */
class Client
{
    protected $api;
    protected $client_id;
    protected $secret;

    public function __construct($host, $client_id, $secret)
    {
        $this->api       = $host;
        $this->host      = $host;
        $this->client_id = $client_id;
        $this->secret    = $secret;
    }

    public function setApi($host)
    {
        $this->api = $host;
        return $this;
    }

    public function setHost($url)
    {
        $this->host = $url;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getApiClient()
    {
         return new GuzzleHttp\Client([
            'base_uri' => $this->api,
            'timeout'  => 30.0,
        ]);
    }

    public function sign(array $data, $secret)
    {
        $data = array_map('strval', array_values(array_filter($data)));
        return substr(hash_hmac('sha256', serialize($data), $secret), 0, 8);
    }

    public function prepare(Array $json = array())
    {
        $json = json_encode(array_merge([
        ], $json));
        try {
            $response = $this->getApiClient()->request('POST', 'create', [
                'headers' => [
                    'X-Client' => $this->client_id,
                    'X-Signature' => hash_hmac('sha256', $json, $this->secret),
                    'Content-Type' => 'application/json',
                ],
                'body' => $json,
            ]);
        } catch (\Exception $e) {
            die((string)$e);
        }

        $response = json_decode((string)$response->getBody());

        return $response;
    }
}

