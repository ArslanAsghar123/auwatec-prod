<?php declare(strict_types=1);

namespace DreiscSeoPro\Command\BulkGenerator;

use DreiscSeoPro\Core\BulkGenerator\CategoryGenerator;
use DreiscSeoPro\Core\BulkGenerator\Message\BulkGeneratorMessageService;
use DreiscSeoPro\Core\Content\DreiscSeoBulk\DreiscSeoBulkEnum;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CategoryCommand extends Command
{
    public function __construct(
        private readonly BulkGeneratorMessageService $bulkGeneratorMessageService,
        private readonly CategoryGenerator $categoryGenerator
    )
    {
        parent::__construct();
    }

    protected function configure() :void
    {
        $this->setName('dreisc-seo:bulk-generator:category')
            ->setDescription('Starts the generation of the category bulk templates')

            ->addOption(
                'interval',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Number of elements being progressed at once',
                25
            )

            ->addOption(
                'seoOptions',
                null,
                InputOption::VALUE_OPTIONAL,
                'Seo options separated by commas that should be created. Possible values: ' . implode(', ', DreiscSeoBulkEnum::VALID_SEO_OPTIONS),
                null
            )

            ->addOption(
                'bulkGeneratorTypes',
                null,
                InputOption::VALUE_OPTIONAL,
                'Bulk generator types separated by commas that should be created. Possible values: ' . implode(', ', DreiscSeoBulkEnum::VALID_BULK_GENERATOR_TYPES),
                null
            )

            ->addOption(
                'languageIds',
                null,
                InputOption::VALUE_OPTIONAL,
                'Language ids separated by commas that should be created.',
                null
            )

            ->addOption(
                'categoryIds',
                null,
                InputOption::VALUE_OPTIONAL,
                'Product ids separated by commas that should be created. If this parameter is specified, the bulk generator for the specified categorys is started directly.',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) :int
    {
        /** Space */
        $output->write("\n");
        $io = new SymfonyStyle($input, $output);

        $interval = (int) $input->getOption('interval');

        $languageIds = $input->getOption('languageIds');
        if(!empty($languageIds)) {
            $languageIds = array_map('trim', explode(',', strtolower($languageIds)));
        }

        $seoOptions = $input->getOption('seoOptions');
        if(!empty($seoOptions)) {
            $seoOptions = array_map('trim', explode(',', $seoOptions));
        }

        $bulkGeneratorTypes = $input->getOption('bulkGeneratorTypes');
        if(!empty($bulkGeneratorTypes)) {
            $bulkGeneratorTypes = array_map('trim', explode(',', $bulkGeneratorTypes));
        }

        $categoryIds = $input->getOption('categoryIds');
        if(!empty($categoryIds)) {
            $categoryIds = array_map('trim', explode(',', $categoryIds));

            $this->categoryGenerator->generate(
                $categoryIds,
                $languageIds ?? [],
                $seoOptions ?? [],
                $bulkGeneratorTypes ?? []
            );
        } else {
            $this->bulkGeneratorMessageService->dispatchBulkGenerator(
                DreiscSeoBulkEnum::AREA__CATEGORY,
                $languageIds,
                $seoOptions,
                $bulkGeneratorTypes,
                empty($interval) ? 25 : $interval,
                $io
            );
        }

        return Command::SUCCESS;
    }
}
