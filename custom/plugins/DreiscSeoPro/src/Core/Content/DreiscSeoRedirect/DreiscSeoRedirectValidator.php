<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\Content\DreiscSeoRedirect;

use DreiscSeoPro\Core\Foundation\Dal\Validator;

class DreiscSeoRedirectValidator extends Validator
{
    final public const VIOLATION_SOURCE_PATH_SHOULD_NOT_START_WITH_SLASH = 'source_path_should_not_start_with_slash';
    final public const VIOLATION_REDIRECT_PATH_SHOULD_NOT_START_WITH_SLASH = 'redirect_path_should_not_start_with_slash';

    protected function getDefinitionClass(): string
    {
        return DreiscSeoRedirectDefinition::class;
    }

    protected function getEntityName(): string
    {
        return DreiscSeoRedirectDefinition::ENTITY_NAME;
    }

    protected function fetchViolations($command): void
    {
        /** Check for source_type */
        $this->violationIfEmpty(DreiscSeoRedirectEntity::SOURCE_TYPE__STORAGE_NAME);

        /** Check for redirect_type */
        $this->violationIfEmpty(DreiscSeoRedirectEntity::REDIRECT_TYPE__STORAGE_NAME);

        /** Check for redirect_type */
        $this->violationIfEmpty(DreiscSeoRedirectEntity::REDIRECT_HTTP_STATUS_CODE__STORAGE_NAME);

        /** Check if the source type is valid */
        if (!empty($this->payload[DreiscSeoRedirectEntity::SOURCE_TYPE__STORAGE_NAME])) {
            $this->violationIfValueNotOnWhitelist(
                DreiscSeoRedirectEntity::SOURCE_TYPE__STORAGE_NAME,
                DreiscSeoRedirectEnum::VALID_SOURCE_TYPES
            );
        }

        /** Check if the redirect type is valid */
        if (!empty($this->payload[DreiscSeoRedirectEntity::REDIRECT_TYPE__STORAGE_NAME])) {
            $this->violationIfValueNotOnWhitelist(
                DreiscSeoRedirectEntity::REDIRECT_TYPE__STORAGE_NAME,
                DreiscSeoRedirectEnum::VALID_REDIRECT_TYPES
            );
        }

        /** Check the fields if the source type is "url" */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::SOURCE_TYPE__STORAGE_NAME,
            DreiscSeoRedirectEnum::SOURCE_TYPE__URL,
            [
                DreiscSeoRedirectEntity::SOURCE_SALES_CHANNEL_DOMAIN_ID__STORAGE_NAME,
                DreiscSeoRedirectEntity::SOURCE_PATH__STORAGE_NAME
            ]
        );

        /** Check the fields if the redirect type is "url" */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::REDIRECT_TYPE__STORAGE_NAME,
            DreiscSeoRedirectEnum::REDIRECT_TYPE__URL,
            [
                DreiscSeoRedirectEntity::REDIRECT_SALES_CHANNEL_DOMAIN_ID__STORAGE_NAME
            ]
        );

        /** Check the fields if the redirect type is "externalUrl" */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::REDIRECT_TYPE__STORAGE_NAME,
            DreiscSeoRedirectEnum::REDIRECT_TYPE__EXTERNAL_URL,
            [
                DreiscSeoRedirectEntity::REDIRECT_URL__STORAGE_NAME
            ]
        );

        /** Check the fields if the redirect type is "product" */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::REDIRECT_TYPE__STORAGE_NAME,
            DreiscSeoRedirectEnum::REDIRECT_TYPE__PRODUCT,
            [
                DreiscSeoRedirectEntity::REDIRECT_PRODUCT_ID__STORAGE_NAME
            ]
        );

        /** Check the fields if the source type is "product" */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::SOURCE_TYPE__STORAGE_NAME,
            DreiscSeoRedirectEnum::SOURCE_TYPE__PRODUCT,
            [
                DreiscSeoRedirectEntity::SOURCE_PRODUCT_ID__STORAGE_NAME
            ]
        );

        /** Check the fields if the redirect type is "category" */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::REDIRECT_TYPE__STORAGE_NAME,
            DreiscSeoRedirectEnum::REDIRECT_TYPE__CATEGORY,
            [
                DreiscSeoRedirectEntity::REDIRECT_CATEGORY_ID__STORAGE_NAME
            ]
        );

        /** Check the fields if the source type is "category" */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::SOURCE_TYPE__STORAGE_NAME,
            DreiscSeoRedirectEnum::SOURCE_TYPE__CATEGORY,
            [
                DreiscSeoRedirectEntity::SOURCE_CATEGORY_ID__STORAGE_NAME
            ]
        );

        /** Check if the field "sourceSalesChannelDomainRestrictionIds" is defined, when the restriction is enabled */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::HAS_SOURCE_SALES_CHANNEL_DOMAIN_RESTRICTION__STORAGE_NAME,
            1,
            [
                DreiscSeoRedirectEntity::SOURCE_SALES_CHANNEL_DOMAIN_RESTRICTION_IDS__STORAGE_NAME
            ]
        );

        /** Check if the field "deviatingRedirectSalesChannelDomainId" is defined, when the deviating redirect domain is enabled */
        $this->violationIfFieldHasSpecialValueAndOtherFieldsAreEmpty(
            DreiscSeoRedirectEntity::HAS_DEVIATING_REDIRECT_SALES_CHANNEL_DOMAIN__STORAGE_NAME,
            1,
            [
                DreiscSeoRedirectEntity::DEVIATING_REDIRECT_SALES_CHANNEL_DOMAIN_ID__STORAGE_NAME
            ]
        );

        /** Check the field "source_path" if it starts with a slash */
        if(!empty($this->payload[DreiscSeoRedirectEntity::SOURCE_PATH__STORAGE_NAME])) {
            $source_path = $this->payload[DreiscSeoRedirectEntity::SOURCE_PATH__STORAGE_NAME];
            if (str_starts_with((string) $source_path, '/')) {
                $this->violations->add(
                    $this->buildViolation(
                        'It is not allowed, that the field {{ field }} starts with a slash',
                        [
                            '{{ field }}' => DreiscSeoRedirectEntity::SOURCE_PATH__STORAGE_NAME
                        ],
                        null,
                        '/' . $this->currentId,
                        $source_path,
                        self::VIOLATION_SOURCE_PATH_SHOULD_NOT_START_WITH_SLASH
                    )
                );
            }
        }

        /** Check the field "redirect_path" if it starts with a slash */
        if(!empty($this->payload[DreiscSeoRedirectEntity::REDIRECT_PATH__STORAGE_NAME])) {
            $redirect_path = $this->payload[DreiscSeoRedirectEntity::REDIRECT_PATH__STORAGE_NAME];
            if (str_starts_with((string) $redirect_path, '/')) {
                $this->violations->add(
                    $this->buildViolation(
                        'It is not allowed, that the field {{ field }} starts with a slash',
                        [
                            '{{ field }}' => DreiscSeoRedirectEntity::REDIRECT_PATH__STORAGE_NAME
                        ],
                        null,
                        '/' . $this->currentId,
                        $redirect_path,
                        self::VIOLATION_REDIRECT_PATH_SHOULD_NOT_START_WITH_SLASH
                    )
                );
            }
        }
    }
}
