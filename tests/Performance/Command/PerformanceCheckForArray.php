<?php
/*
 * This file is part of phindexer package.
 *
 * (c) 2018 Nicolas Marniesse <nicolas.marniesse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NMarniesse\Phindexer\Test\Performance\Command;

use Faker\Factory;
use NMarniesse\Phindexer\Collection\ArrayCollection;
use NMarniesse\Phindexer\CollectionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PerformanceCheckForArray
 *
 * @package NMarniesse\Phindexer\Test\Performance\Command
 * @author Nicolas Marniesse <nicolas.marniesse@phc-holding.com>
 */
class PerformanceCheckForArray extends Command
{
    /**
     * configure
     */
    protected function configure()
    {
        $this
            ->setName('performance:array:launch')
            ->setDescription('Launch performance test on array data.')
            ->addArgument(
                'elements',
                InputArgument::OPTIONAL,
                'Number of elements in tested data.',
                10000
            )
            ->addArgument(
                'searches',
                InputArgument::OPTIONAL,
                'Number of searches on data.',
                1000
            )
            ->addOption(
                'strategy',
                's',
                InputOption::VALUE_OPTIONAL,
                'Search using given strategy: phindexer or classic).',
                'phindexer'
            )
        ;
    }

    /**
     * execute
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $memory_start = memory_get_usage();

        $output->write('Create fixtures... ');
        $array = $this->createDataFixtures($input->getArgument('elements'));
        $output->writeln('<info>OK</info>');

        $strategy = $input->getOption('strategy');
        if ($strategy === 'phindexer') {
            $data = new ArrayCollection($array);
            $data->addColumnIndex('first_name');
            $data->addColumnIndex('email');
        } else {
            $data = $array;
        }

        $time_start = microtime(true);
        $output->write('Job starts... ');

        $faker = Factory::create();
        $searches = $input->getArgument('searches');
        for ($i = 0; $i < $searches / 2; $i++) {
            $this->search($strategy, $data, 'first_name', $faker->firstName);
            $this->search($strategy, $data, 'email', $faker->email);
        }

        $output->writeln('<info>OK</info>');

        $output->writeln([
            '',
            'Results',
            '-------',
            sprintf('Strategy    : %s', $strategy),
            sprintf('Data size   : %d', count($array)),
            sprintf('Ran searches: %d', $searches),
            sprintf('Memory used : %f MB', max((memory_get_usage() - $memory_start) / 1048576, 0)),
            sprintf('Time        : %f seconds', microtime(true) - $time_start),
        ]);
    }

    /**
     * @param string   $strategy
     * @param iterable $list
     * @param string   $column
     * @param string   $search_value
     * @return PerformanceCheckForArray
     */
    protected function search(string $strategy, iterable $list, string $column, string $search_value): self
    {
        if ($strategy === 'phindexer') {
            $this->searchInDataUsingPhindexer($list, $column, $search_value);
        } else {
            $this->searchInDataUsingClassicStrategy($list, $column, $search_value);
        }

        return $this;
    }

    /**
     * searchInDataUsingPhindexer
     *
     * @param CollectionInterface $collection
     * @param string              $column
     * @param string              $value
     * @return CollectionInterface
     */
    protected function searchInDataUsingPhindexer(
        CollectionInterface $collection,
        string $column,
        string $value
    ): CollectionInterface {
        return $collection->findWhere($column, $value);
    }

    /**
     * searchInDataUsingClassicStrategy
     *
     * @param iterable $data
     * @param string   $column
     * @param string   $value
     * @return array
     */
    protected function searchInDataUsingClassicStrategy(iterable $data, string $column, string $value): array
    {
        $res = [];
        foreach ($data as $row) {
            if ($row[$column] === $value) {
                $res[] = $row;
            }
        }

        return $res;
    }

    /**
     * @param int $elements
     * @return array
     */
    protected function createDataFixtures(int $elements): array
    {
        $faker = Factory::create();

        $data = [];
        for ($i = 0; $i < $elements; $i++) {
            $data[] = [
                'id'         => $faker->uuid,
                'first_name' => $faker->firstName,
                'last_name'  => $faker->lastName,
                'email'      => $faker->email,
                'address'    => $faker->address,
            ];
        }

        return $data;
    }
}
