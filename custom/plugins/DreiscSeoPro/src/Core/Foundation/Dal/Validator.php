<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Foundation\Dal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidLengthException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use function strtolower;

abstract class Validator implements EventSubscriberInterface
{
    final public const VIOLATION_FIELD_SHOULD_NOT_BE_BLANK = 'field_should_not_be_blank';
    final public const VIOLATION_INVALID_VALUE_FOR_THE_FIELD = 'invalid_value_for_the_field';
    final public const VIOLATION_FIELD_HAS_SPECIAL_VALUE_AND_OTHER_FIELDS_ARE_REQUIRED = 'field_has_special_value_and_other_fields_are_required';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string|null
     */
    protected $currentId;

    /**
     * @var array|null
     */
    protected $payload;

    /**
     * @var ConstraintViolationList
     */
    protected $violations;

    abstract protected function getDefinitionClass(): string;
    abstract protected function getEntityName(): string;
    abstract protected function fetchViolations(WriteCommand $command): void;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'preValidate'
        ];
    }

    public function preValidate(PreWriteValidationEvent $event): void
    {
        $commands = $event->getCommands();
        $this->violations = new ConstraintViolationList();

        foreach ($commands as $command) {
            /** Abort if its not a insert or update */
            if (!$command instanceof InsertCommand && !$command instanceof UpdateCommand) {
                continue;
            }

            /** Abort if not a transaction of the current entity */
            if ($command->getDefinition()->getClass() !== $this->getDefinitionClass()) {
                continue;
            }

            /** Params of the insert/update */
            $this->payload = $command->getPayload();

            /** Fetch the id of the entity */
            $primaryKeys = $command->getPrimaryKey();
            $this->currentId = strtolower((string) Uuid::fromBytesToHex($primaryKeys['id']));

            /**
             * Load the existing data, if this an update and merge this
             * data with the new one. After this we are able to check the
             * complete data set
             */
            if($command instanceof UpdateCommand && !empty($primaryKeys['id'])) {
                $statement = $this->connection->executeQuery('
                    SELECT *
                    FROM ' . $this->getEntityName() . '
                    WHERE `id` = :id
                ', [
                        'id' => $primaryKeys['id']
                    ]
                );

                $existingData = $statement->fetch(FetchMode::ASSOCIATIVE);

                /** Merge the payload in the existing data array */
                foreach($this->payload as $key => $value) {
                    $existingData[$key] = $value;
                }

                $this->payload = $existingData;
            }

            /** Fetch the violations */
            $this->fetchViolations($command);
        }

        if ($this->violations->count() > 0) {
            $event->getExceptions()->add(new WriteConstraintViolationException($this->violations));
        }
    }

    /**
     * Checks if a field is empty
     */
    protected function violationIfEmpty(string $storageName): void
    {
        if (empty($this->payload[$storageName])) {
            $this->violations->add(
                $this->buildViolation(
                    'The field "{{ field }}" should not be blank',
                    [
                        '{{ field }}' => $storageName
                    ],
                    null,
                    '/' . $this->currentId,
                    '',
                    self::VIOLATION_FIELD_SHOULD_NOT_BE_BLANK
                )
            );
        }
    }

    /**
     * Add a violation if the value of the given field is not on the whitelist
     */
    protected function violationIfValueNotOnWhitelist(string $storageName, array $whitelist): void
    {
        if (!in_array($this->payload[$storageName], $whitelist, true)) {
            $this->violations->add(
                $this->buildViolation(
                    'Invalid value for the field "{{ field }}". Possible values are: ' . implode(', ', $whitelist),
                    [
                        '{{ field }}' => $storageName
                    ],
                    null,
                    '/' . $this->currentId,
                    $this->payload[$storageName],
                    self::VIOLATION_INVALID_VALUE_FOR_THE_FIELD
                )
            );
        }
    }

    /**
     * Add a violation if the $field has the $value and one of the fields in the list ($notEmptyFields) is empty
     */
    protected function violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(string $field, mixed $value, array $notEmptyFields): void
    {
        /** Abort, if the field has not the given value */
        if(empty($this->payload[$field]) || $this->payload[$field] !== $value) {
            return;
        }

        $invalidFieldNames = [];
        foreach($notEmptyFields as $notEmptyField) {
            if (empty($this->payload[$notEmptyField])) {
                $invalidFieldNames[] = $notEmptyField;
            }
        }

        /** Abort, if no invalid field was found */
        if(empty($invalidFieldNames)) {
            return;
        }

        $this->violations->add(
            $this->buildViolation(
                'The value of the field "{{ field }}" is "{{ value }}". In this case the following fields should not be blank: ' . implode(', ', $invalidFieldNames),
                [
                    '{{ field }}' => $field,
                    '{{ value }}' => $value
                ],
                null,
                '/' . $this->currentId,
                '',
                self::VIOLATION_FIELD_HAS_SPECIAL_VALUE_AND_OTHER_FIELDS_ARE_REQUIRED
            )
        );
    }

    /**
     * Unique-check
     *
     * Add a violation if the is already an entity where the database fields with the
     * given storage names have the same values
     *
     * @param WriteCommand $command
     * @param array $storageNames
     * @throws DBALException
     * @throws InvalidUuidException
     * @throws InvalidUuidLengthException
     */
    protected function violationIfThereIsAlreadyAnEntityWithTheSameValues(WriteCommand $command, array $storageNames): void
    {
        $payload = $command->getPayload();
        $conditions = [];
        $parameters = [];

        foreach($storageNames as $storageName) {
            if (empty($payload[$storageName])) {
                /** Field was not set in the payload, so we check for empty or null */
                $conditions[] = sprintf(
                    '(`%1$s` = \'\' OR `%1$s` IS NULL)',
                    $storageName
                );
            } else {
                /** Field was set, so we check for given value */
                $parameters[$storageName] = $payload[$storageName];
                $conditions[] = sprintf(
                    '`%1$s` = :%1$s',
                    $storageName
                );
            }
        }

        /** Exclude the own entity, if it's an update */
        $primaryKeys = $command->getPrimaryKey();
        if($command instanceof UpdateCommand && is_array($primaryKeys)) {
            foreach($primaryKeys as $primaryKey => $primaryKeyValue) {
                $parameters[$primaryKey] = $primaryKeyValue;
                $conditions[] = sprintf(
                    '`%1$s` != :%1$s',
                    $primaryKey
                );
            }
        }

        /** Check if we find a duplicate */
        $statement = $this->connection->executeQuery('
            SELECT *
            FROM ' . $this->getEntityName() . '
            WHERE ' . implode(' AND ', $conditions) . '
        ', $parameters);

        $existingData = $statement->fetch(FetchMode::ASSOCIATIVE);
        if($existingData !== false) {
            $this->violations->add(
                $this->buildViolation(
                    'There is already an entity with the unique field values for "{{ fields }}" » see {{ entityName }}.id: {{ id }}',
                    [
                        '{{ fields }}' => implode(', ', $storageNames),
                        '{{ entityName }}' => $this->getEntityName(),
                        '{{ id }}' => !empty($payload['id']) ? Uuid::fromBytesToHex($payload['id']) : '[missing id field]'
                    ],
                    null,
                    '/' . $this->currentId,
                    '',
                    self::VIOLATION_FIELD_SHOULD_NOT_BE_BLANK
                )
            );
        }
    }

    /**
     * @param null $root
     * @param null $invalidValue
     * @param null $code
     */
    protected function buildViolation(
        string $messageTemplate,
        array $parameters,
        $root = null,
        ?string $propertyPath = null,
        $invalidValue = null,
        $code = null
    ): ConstraintViolationInterface {
        return new ConstraintViolation(
            $this->strReplace(array_keys($parameters), array_values($parameters), $messageTemplate),
            $messageTemplate,
            $parameters,
            $root,
            $propertyPath,
            $invalidValue,
            $plural = null,
            $code,
            $constraint = null,
            $cause = null
        );
    }

	/**
	 * PHP 8.0 Support
	 */
	public function strReplace($search, $replace, $subject)
	{
		if(empty($search) || empty($replace) || empty($subject)) {
			return $subject;
		}

		return str_replace($search, $replace, (string) $subject);
	}
}
