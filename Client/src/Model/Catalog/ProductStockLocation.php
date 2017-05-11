<?php
declare(strict_types=1);

namespace Attlaz\Model\Catalog;

class ProductStockLocation
{
    const BASE_LOCATION = 'base';
    private $id, $code, $name;

    public function __construct(int $id = 0, string $code = self::BASE_LOCATION)
    {
        $this->setId($id);
        $this->setCode($code);
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }
}
