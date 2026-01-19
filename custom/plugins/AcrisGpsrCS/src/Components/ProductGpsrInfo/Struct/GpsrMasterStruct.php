<?php declare(strict_types=1);

namespace Acris\Gpsr\Components\ProductGpsrInfo\Struct;

use Shopware\Core\Framework\Struct\Struct;

class GpsrMasterStruct extends Struct
{
    protected GpsrInfoCollection $types;
    protected GpsrInfoCollection $manufacturers;
    protected GpsrInfoCollection $contacts;
    protected GpsrInfoCollection $notes;

    public function __construct(GpsrInfoCollection $types = new GpsrInfoCollection(), GpsrInfoCollection $manufacturers = new GpsrInfoCollection(), GpsrInfoCollection $contacts = new GpsrInfoCollection(), GpsrInfoCollection $notes = new GpsrInfoCollection())
    {
        $this->types = $types;
        $this->manufacturers = $manufacturers;
        $this->contacts = $contacts;
        $this->notes = $notes;
    }

    public function getTypes(): GpsrInfoCollection
    {
        return $this->types;
    }

    public function setTypes(GpsrInfoCollection $types): void
    {
        $this->types = $types;
    }

    public function getManufacturers(): GpsrInfoCollection
    {
        return $this->manufacturers;
    }

    public function setManufacturers(GpsrInfoCollection $manufacturers): void
    {
        $this->manufacturers = $manufacturers;
    }

    public function getContacts(): GpsrInfoCollection
    {
        return $this->contacts;
    }

    public function setContacts(GpsrInfoCollection $contacts): void
    {
        $this->contacts = $contacts;
    }

    public function getNotes(): GpsrInfoCollection
    {
        return $this->notes;
    }

    public function setNotes(GpsrInfoCollection $notes): void
    {
        $this->notes = $notes;
    }

    public function getByDisplayTypeAndPositionGrouped(string $displayType, string $position): GpsrInfoGroupedCollection
    {
        $gpsrInfoGroupedCollection = new GpsrInfoGroupedCollection();
        $collection = $this->getByDisplayTypeAndPosition($displayType, $position);
        foreach ($collection->getElements() as $gpsrInfoStruct) {
            $gpsrInfoCollection = $gpsrInfoGroupedCollection->has($gpsrInfoStruct->getContentType()) ? $gpsrInfoGroupedCollection->get($gpsrInfoStruct->getContentType()) : new GpsrInfoCollection();
            $gpsrInfoCollection->add($gpsrInfoStruct);
            $gpsrInfoGroupedCollection->set($gpsrInfoStruct->getContentType(), $gpsrInfoCollection);
        }
        return $gpsrInfoGroupedCollection;
    }

    public function getByDisplayTypeAndPosition(string $displayType, string $position): GpsrInfoCollection
    {
        $collection = new GpsrInfoCollection();
        foreach ($this->types as $type) {
            if ($type->getDisplayType() === $displayType && $type->getTabPosition() === $position) {
                $collection->add($type);
            }
        }
        foreach ($this->manufacturers as $manufacturer) {
            if ($manufacturer->getDisplayType() === $displayType && $manufacturer->getTabPosition() === $position) {
                $collection->add($manufacturer);
            }
        }
        foreach ($this->contacts as $contact) {
            if ($contact->getDisplayType() === $displayType && $contact->getTabPosition() === $position) {
                $collection->add($contact);
            }
        }
        foreach ($this->notes as $note) {
            if ($note->getDisplayType() === $displayType && $note->getTabPosition() === $position) {
                $collection->add($note);
            }
        }

        // sort by prio desc
        $collection->sort(function (GpsrInfoStruct $a, GpsrInfoStruct $b) {
            return $b->getPriority() <=> $a->getPriority();
        });
        return $collection;
    }

    public function getByDisplayDescriptionPosition(string $position): GpsrInfoCollection
    {
        $displayType = GpsrInfoStruct::DISPLAY_TYPE_DESCRIPTION;
        $collection = new GpsrInfoCollection();
        foreach ($this->types as $type) {
            if ($type->getDisplayType() === $displayType && $type->getDescriptionPosition() === $position) {
                $collection->add($type);
            }
        }
        foreach ($this->manufacturers as $manufacturer) {
            if ($manufacturer->getDisplayType() === $displayType && $manufacturer->getDescriptionPosition() === $position) {
                $collection->add($manufacturer);
            }
        }
        foreach ($this->contacts as $contact) {
            if ($contact->getDisplayType() === $displayType && $contact->getDescriptionPosition() === $position) {
                $collection->add($contact);
            }
        }
        foreach ($this->notes as $note) {
            if ($note->getDisplayType() === $displayType && $note->getDescriptionPosition() === $position) {
                $collection->add($note);
            }
        }

        // sort by prio desc
        $collection->sort(function (GpsrInfoStruct $a, GpsrInfoStruct $b) {
            return $b->getPriority() <=> $a->getPriority();
        });
        return $collection;
    }
}
