<?php declare(strict_types=1);

namespace Cbax\ModulLexicon\Core\Content\Events;

use Symfony\Contracts\EventDispatcher\Event;

class CbaxLexiconEntriesLoadedEvent extends Event
{

    public const NAME = 'cbax.lexicon.entriesloaded';

    public function __construct(
        private array  $entries
    ) {

    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    public function setEntries(array $entries): void
    {
        $this->entries = $entries;
    }

}
