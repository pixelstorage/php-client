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

/**
 * Image class
 *
 * This class abstract all the operations supported by the PixelStorage server. 
 *
 * It exposes a fluent interface, implementing most operations (for performance reasons)
 * but also using the magic `__call` method to be compatible in the future.
 */
class Image
{
    protected $image_id;
    protected $secret;
    protected $client;

    protected $filters = [];

    /**
     * 
     */
    public function __construct($image_id, $secret, Client $client)
    {
        $this->image_id = $image_id;
        $this->secret   = $secret;
        $this->client   = $client;
    }

    protected function between($name, $level, $min, $max)
    {
        if ($level > $max || $level < $min) {
            throw new RuntimeException("Invalid value for $name, it should be between $min and $max");
        }
        return $level;
    }

    /**
     * Generic method to queue a filter with their argument.
     *
     * This method makes it future-compatible with newer (and customer filters)
     *
     * @return $this
     */
    public function __call($method, array $args)
    {
        $this->filters[] = array_merge($method, $args);
        return $this;
    }

    /**
     * Crops and resized an image at the same time
     */
    public function fit($width, $height = null, $position = 'center')
    {
        $this->filters[] = [__FUNCTION__, $width, $height ?: $width, $position];
        return $this;
    }

    public function flip($mode)
    {
        $mode = in_array($mode, [2, 'v', 'vert', 'vertical']) ? 'v' : 'h';
        $this->filters[] = [__FUNCTION__, $mode];
        return $this;
    }

    /**
     * Applies blur effect on image
     */
    public function blur($amount = 1)
    {
        $this->filters[] = [__FUNCTION__, $this->between('amount', $amount, 0, 100)];
        return $this;
    }

    /**
     * Applies gamma correction to a given image
     */
    public function gamma($gamma)
    {
        $this->filters[] = [__FUNCTION__, $gamma];
        return $this;
    }

    /**
     * Turns an image into a greyscale version
     */
    public function greyscale()
    {
        $this->filters[] = [__FUNCTION__];
        return $this;
    }

    /**
     * Resize image proportionally to given height
     */
    public function heighten($height)
    {
        $this->filters[] = [__FUNCTION__, $height];
        return $this;
    }

    /**
     * Toggles interlaced encoding mode
     */
    public function interlace($mode)
    {
        $this->filters[] = [__FUNCTION__, (int)$mode];
        return $this;
    }

    /**
     * Inverts colors of an image
     */
    public function invert()
    {
        $this->filters[] = [__FUNCTION__];
        return $this;
    }

    /**
     * Defines opacity of an image
     */
    public function opacity($transparency)
    {
        $this->filters[] = [__FUNCTION__, $this->between('transparency', $transparency, 0, 100)];
        return $this;
    }

    /**
     * Applies a pixelation effect to a given image
     */
    public function pixelate($size)
    {
        $this->filters[] = [__FUNCTION__, $size];
        return $this;
    }

    /**
     * Reduces colors of a given image
     */
    public function limitcolors($count, $matte)
    {
        $this->filters[] = [__FUNCTION__, $count, $matte];
        return $this;
    }

    /**
     * Crops the image
     */
    public function crop($width, $height, $x = 0, $y = 0)
    {
        $this->filters[] = [__FUNCTION__, $width, $height, $x, $y];
        return $this;
    }

    public function brightness($level)
    {
        $this->filters[] = [__FUNCTION__, $this->between('level', $level, -100, 100)];
        return $this;
    }

    /**
     * Changes contrast of image
     */
    public function contrast($level)
    {
        $this->filters[] = [__FUNCTION__, $this->between('level', $level, -100, 100)];
        return $this;
    }

    /**
     * Changes balance of different RGB color channels
     */
    public function colorize($red, $green, $blue)
    {
        $this->filters[] = [
            __FUNCTION__,
            $this->between('red', $red, -100, 100),
            $this->between('green', $red, -100, 100),
            $this->between('blue', $red, -100, 100),
        ];
        return $this;
    }

    /**
     * Resizes image dimensions
     */
    public function resize($width, $height=null)
    {
        if ($height) {
            $this->filters[] = [__FUNCTION__, $width, $height];
        } else {
            $this->filters[] = [__FUNCTION__, $width];
        }
        return $this;
    }

    /**
     * Rotates image counter clockwise
     */
    public function rotate($angle)
    {
        $this->filters[] = [__FUNCTION__, $angle];
        return $this;
    }

    /**
     * Sharpen image
     */
    public function sharpen($amount)
    {
        $this->filters[] = [__FUNCTION__, $this->between('amount', $amount, 0, 100)];
        return $this;
    }

    /**
     * Trims away parts of an image
     */
    public function trim($base, $away, $tolerance, $feather)
    {
        $this->filters[] = [__FUNCTION__, $base, $away, $tolerance, $feather];
        return $this;
    }

    /**
     * Resize image proportionally to given width
     */
    public function widen($width)
    {
        $this->filters[] = [__FUNCTION__, $width];
        return $this;
    }

    public function url()
    {
        $uri = [];
        foreach ($this->filters as $filter) {
            $uri = array_merge($uri, (array)$filter); 
        }

        return $this->client->getHost() . '/i/' 
            . $this->image_id . '/' 
            . implode("/", $uri)
            . '/' . $this->client->sign($uri, $this->secret);
    }

    public function __toString()
    {
        return $this->url();
    }
}
