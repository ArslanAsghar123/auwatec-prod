<?php declare(strict_types=1);

namespace Acris\Gpsr\Components\ProductGpsrInfo\Struct;

use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Uuid\Uuid;

class GpsrInfoStruct extends Struct
{
    public const CONTENT_TYPE_TYPE = 'type';
    public const CONTENT_TYPE_MANUFACTURER = 'manufacturer';
    public const CONTENT_TYPE_CONTACT = 'contact';
    public const CONTENT_TYPE_NOTE = 'note';

    public const DISPLAY_TYPE_GPSR_TAB = 'gpsrTab';
    public const DISPLAY_TYPE_TAB = 'tab';
    public const DISPLAY_TYPE_DESCRIPTION = 'description';

    public const TAB_POSITION_BEFORE_DESCRIPTION_TAB = 'beforeDescriptionTab';
    public const TAB_POSITION_AFTER_DESCRIPTION_TAB = 'afterDescriptionTab';
    public const TAB_POSITION_AFTER_REVIEWS_TAB = 'afterReviewsTab';

    public const DESCRIPTION_DISPLAY_MODAL = 'modal';
    public const DESCRIPTION_DISPLAY_AMONG_EACH_OTHER = 'amongEachOther';

    public const DESCRIPTION_POSITION_BEFORE_DESCRIPTION = 'beforeDescription';
    public const DESCRIPTION_POSITION_AFTER_DESCRIPTION = 'afterDescription';

    public const DISPLAY_SEPARATOR_SHOW = 'show';
    public const DISPLAY_SEPARATOR_HIDE = 'hide';

    protected string $id;

    protected string $contentType = self::CONTENT_TYPE_MANUFACTURER;

    protected string $displayType = self::DISPLAY_TYPE_DESCRIPTION;
    protected string $tabPosition = self::TAB_POSITION_AFTER_REVIEWS_TAB;
    protected string $descriptionDisplay = self::DESCRIPTION_DISPLAY_AMONG_EACH_OTHER;
    protected string $descriptionPosition = self::DESCRIPTION_POSITION_AFTER_DESCRIPTION;
    protected string $displaySeparator = self::DISPLAY_SEPARATOR_HIDE;

    protected ?string $headline = null;
    protected ?string $text = null;

    protected bool $hasAddress = false;

    protected ?string $name = null;
    protected ?string $street = null;
    protected ?string $houseNumber = null;
    protected ?string $city = null;
    protected ?string $zipcode = null;
    protected ?string $country = null;
    protected ?string $phoneNumber = null;
    protected ?string $address = null;

    protected ?string $modalInfoText = null;
    protected ?string $modalLinkText = null;

    protected ?int $priority = 10;
    protected $documents = [];

    public function __construct(?string $id = null)
    {
        if(empty($id)) $id = Uuid::randomHex();
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    public function setDisplayType(string $displayType): void
    {
        $this->displayType = $displayType;
    }

    public function getTabPosition(): string
    {
        return $this->tabPosition;
    }

    public function setTabPosition(?string $tabPosition): void
    {
        if(empty($tabPosition)) $tabPosition = self::TAB_POSITION_AFTER_REVIEWS_TAB;
        $this->tabPosition = $tabPosition;
    }

    public function getDescriptionDisplay(): string
    {
        return $this->descriptionDisplay;
    }

    public function setDescriptionDisplay(string $descriptionDisplay): void
    {
        $this->descriptionDisplay = $descriptionDisplay;
    }

    public function getDescriptionPosition(): string
    {
        return $this->descriptionPosition;
    }

    public function setDescriptionPosition(string $descriptionPosition): void
    {
        $this->descriptionPosition = $descriptionPosition;
    }

    public function getDisplaySeparator(): string
    {
        return $this->displaySeparator;
    }

    public function setDisplaySeparator(string $displaySeparator): void
    {
        $this->displaySeparator = $displaySeparator;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(?string $headline): void
    {
        $this->headline = $headline;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function isHasAddress(): bool
    {
        return $this->hasAddress;
    }

    public function setHasAddress(bool $hasAddress): void
    {
        $this->hasAddress = $hasAddress;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber): void
    {
        $this->houseNumber = $houseNumber;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getModalInfoText(): ?string
    {
        return $this->modalInfoText;
    }

    public function setModalInfoText(?string $modalInfoText): void
    {
        $this->modalInfoText = $modalInfoText;
    }

    public function getModalLinkText(): ?string
    {
        return $this->modalLinkText;
    }

    public function setModalLinkText(?string $modalLinkText): void
    {
        $this->modalLinkText = $modalLinkText;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }

    public function updateHasAddress(): void
    {
        $this->getName()
        || $this->getStreet()
        || $this->getHouseNumber()
        || $this->getZipcode()
        || $this->getCity()
        || $this->getCountry()
        || $this->getPhoneNumber()
        || $this->getAddress()
            ? $this->setHasAddress(true) : $this->setHasAddress(false);
    }

    public function getDocumentUrls(): array
    {
        return $this->documents;
    }

    public function setDocumentsUrls(array $documents): void
    {
        $this->documents = $documents;
    }
}
