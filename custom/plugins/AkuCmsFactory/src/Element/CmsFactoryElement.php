<?php declare(strict_types=1);

namespace AkuCmsFactory\Element;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class CmsFactoryElement extends Entity {
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var text
     */
    protected $twig;

    /**
     * @var string
     */
    protected $fields;

    public function getName(): string {
        return strval($this->name);
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setTwig(string $twig): void {
        $this->twig = $twig;
    }

    public function getTwig(): string {
        return strval($this->twig);
    }

    public function getFields(): string {
        return strval($this->fields);
    }

    public function setFields(string $fields): void {
        $this->fields = $fields;
    }

}