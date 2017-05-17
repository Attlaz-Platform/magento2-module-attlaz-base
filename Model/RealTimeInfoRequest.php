<?php
declare(strict_types=1);

namespace Attlaz\Base\Model;

class RealTimeInfoRequest implements \JsonSerializable
{
    const TYPE_STOCK = 'stock';
    const TYPE_PRICE = 'price';

    public $product, $block, $template, $data, $type, $requestId;

    public function __construct($product, $block, $type, array $data = [], $template = null)
    {
        $this->product = $product;
        $this->block = $block;

        $this->data = $data;
        $this->type = $type;
        $this->template = $template;
    }

    public function jsonSerialize(): array
    {
        return [
            'product'  => $this->product,
            'block'    => $this->block,
            'type'     => $this->type,
            'data'     => $this->data,
            'template' => $this->template,
        ];
    }

    public static function fromJson(string $json): RealTimeInfoRequest
    {
        $rawData = json_decode(base64_decode($json), true);

        $product = $rawData['product'];
        $block = $rawData['block'];
        $type = $rawData['type'];
        $data = $rawData['data'];
        $template = $rawData['template'];

        return new RealTimeInfoRequest($product, $block, $type, $data, $template);
    }
}