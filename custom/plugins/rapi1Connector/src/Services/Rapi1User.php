<?php

namespace Rapidmail\Shopware\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Rapidmail\Shopware\Repositories\UserAccessKeyRepository;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\User\Aggregate\UserAccessKey\UserAccessKeyEntity;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class Rapi1User
{
    private const PLUGIN_USERNAME = 'rapi1Connector';

    private ?UserAccessKeyRepository $userAccessKeyRepository = null;
    private ?Connection $connection = null;

    /**
     * @throws Exception
     */
    public function create(): void
    {
        if ($this->userExists()) {
            return;
        }

        $builder = $this->getConnection()->createQueryBuilder();

        $localeId = $builder->select('locale.id')
            ->from('language', 'language')
            ->innerJoin('language', 'locale', 'locale', 'language.locale_id = locale.id')
            ->where('language.id = :id')
            ->setParameter('id', Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM))
            ->executeQuery()
            ->fetchOne();

        $userId = Uuid::randomBytes();
        $this->getConnection()->insert('user', [
            'id' => $userId,
            'first_name' => 'Rapidmail',
            'last_name' => 'Newsletter & E-Mail Marketing Integration',
            'email' => 'support@rapidmail.com',
            'username' => self::PLUGIN_USERNAME,
            'password' => password_hash(
                Random::getAlphanumericString(max(5, 8)), \PASSWORD_BCRYPT
            ),
            'locale_id' => strval($localeId),
            'active' => true,
            'admin' => 0,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $aclRoleId = Uuid::randomBytes();
        $this->getConnection()->insert('acl_role', [
            'id' => $aclRoleId,
            'name' => self::PLUGIN_USERNAME . ' RO',
            'description' => 'Read Only permissions granted for the Rapi1Connector plugin',
            'privileges' => '["acl_role:read", "app:read", "app_payment_method:read", "app_script_condition:read", "category.viewer", "category:read", "category_translation:read", "cms.viewer", "cms_block:read", "cms_page:read", "cms_section:read", "cms_slot:read", "country.viewer", "country:read", "country_state:read", "currencies.viewer", "currency:read", "currency_country_rounding:read", "custom_field.viewer", "custom_field:read", "custom_field_set:read", "custom_field_set_relation:read", "customer.viewer", "customer:read", "customer_address:read", "customer_group:read", "customer_groups.viewer", "customer_tag:read", "deleted_entity:read", "delivery_time:read", "delivery_times.viewer", "document.viewer", "document:read", "document_base_config:read", "document_base_config_sales_channel:read", "document_type:read", "flow.viewer", "flow:read", "flow_sequence:read", "integration.viewer", "integration:read", "landing_page.viewer", "landing_page:read", "landing_page_sales_channel:read", "landing_page_tag:read", "landing_page_translation:read", "language.viewer", "language:read", "locale:read", "log_entry:create", "mail_header_footer:read", "mail_template:read", "mail_template_media:read", "mail_template_sales_channel:read", "mail_template_type:read", "mail_templates.viewer", "main_category:read", "media.viewer", "media:read", "media_default_folder:read", "media_folder:read", "media_folder_configuration:read", "media_tag:read", "media_thumbnail_size:read", "message_queue_stats:read", "newsletter_recipient.viewer", "newsletter_recipient:read", "newsletter_recipient_tag:read", "number_range:read", "number_range_sales_channel:read", "number_range_state:read", "number_range_type:read", "number_ranges.viewer", "order.viewer", "order:delete", "order:read", "order_address:read", "order_customer:read", "order_delivery:read", "order_line_item:read", "order_refund.viewer", "order_tag:read", "order_transaction:read", "order_transaction_capture_refund:read", "payment.viewer", "payment_method:read", "plugin:read", "product.viewer", "product:read", "product_category:read", "product_configurator_setting:read", "product_cross_selling:read", "product_cross_selling_assigned_products:read", "product_download:read", "product_export:read", "product_feature_set:read", "product_feature_sets.viewer", "product_manufacturer.viewer", "product_manufacturer:read", "product_media:read", "product_price:read", "product_property:read", "product_review:read", "product_search_config.viewer", "product_search_config:read", "product_search_config_field:read", "product_search_keyword:read", "product_sorting:read", "product_stream.viewer", "product_stream:read", "product_stream_filter:read", "product_tag:read", "product_visibility:read", "promotion.viewer", "promotion:read", "promotion_discount:read", "promotion_discount_prices:read", "promotion_discount_rule:read", "promotion_individual_code:read", "promotion_sales_channel:read", "promotion_setgroup:read", "promotion_setgroup_rule:read", "property.viewer", "property_group:read", "property_group_option:read", "review.viewer", "rule.viewer", "rule:read", "rule_condition:read", "sales_channel.viewer", "sales_channel:read", "sales_channel_analytics:read", "sales_channel_domain:read", "sales_channel_type:read", "salutation.viewer", "salutation:read", "scale_unit.viewer", "seo_url:read", "shipping.viewer", "shipping_method:read", "shipping_method_price:read", "shipping_method_tag:read", "snippet.viewer", "snippet_set:read", "state_machine:read", "state_machine_history:read", "state_machine_state:read", "state_machine_transition:read", "system:clear:cache", "system_config:read", "tag.viewer", "tag:read", "tax.viewer", "tax:read", "tax_provider:read", "tax_rule:read", "tax_rule_type:read", "theme.viewer", "theme:read", "theme_child:read", "unit:read", "user:read", "user_access_key:read", "user_config:create", "user_config:read", "user_config:update", "users_and_permissions.viewer", "version:delete"]',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $this->getConnection()->insert('acl_user_role', [
            'user_id' => $userId,
            'acl_role_id' => $aclRoleId,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    /**
     * @throws Exception
     */
    public function delete(): void
    {
        if (!$this->userExists()) {
            return;
        }

        $this->getConnection()->delete('user', [
            'username' => self::PLUGIN_USERNAME,
        ]);

        $this->getConnection()->delete('acl_role', [
            'name' => self::PLUGIN_USERNAME . ' RO',
        ]);
    }

    /**
     * @throws Exception
     * @throws UserNotFoundException
     */
    public function createAccessKey(Context $context): UserAccessKeyEntity
    {
        $userId = $this->getUserId() ?? throw new UserNotFoundException();

        return $this->userAccessKeyRepository->createUserAccessKey($context, $userId);
    }

    /**
     * @throws Exception
     */
    private function userExists(): bool
    {
        return $this->getConnection()->createQueryBuilder()
                ->select('1')
                ->from('user')
                ->where('username = :username')
                ->setParameter('username', self::PLUGIN_USERNAME)
                ->executeQuery()
                ->rowCount() > 0;
    }

    /**
     * @throws Exception
     */
    private function getUserId(): ?string
    {
        $userId = $this->getConnection()->createQueryBuilder()
            ->select('id')
            ->from('user')
            ->where('username = :username')
            ->setParameter('username', self::PLUGIN_USERNAME)
            ->fetchOne();

        return Uuid::fromBytesToHex($userId);
    }

    public function setUserAccessKeyRepository(UserAccessKeyRepository $userAccessKeyRepository): self
    {
        $this->userAccessKeyRepository = $userAccessKeyRepository;

        return $this;
    }

    public function getUserAccessKeyRepository(): ?UserAccessKeyRepository
    {
        return $this->userAccessKeyRepository;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setConnection(ContainerInterface $container): self
    {
        $this->connection = $container->get(Connection::class);

        return $this;
    }

    public function getConnection(): ?Connection
    {
        return $this->connection;
    }
}
