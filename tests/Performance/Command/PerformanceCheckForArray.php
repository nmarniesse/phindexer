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
use NMarniesse\Phindexer\IndexType\ExpressionIndex;
use NMarniesse\Phindexer\Test\Performance\Job\ClassicJob;
use NMarniesse\Phindexer\Test\Performance\Job\JobInterface;
use NMarniesse\Phindexer\Test\Performance\Job\PhindexerJob;
use NMarniesse\Phindexer\Test\Performance\Job\Decorator\ProfilerJob;
use NMarniesse\Phindexer\Test\Performance\Job\Decorator\RepetitiveJob;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        $output->write('Create fixtures... ');
        $array = $this->createDataFixtures($input->getArgument('elements'));
        $output->writeln('<info>OK</info>');

        $searches = $input->getArgument('searches');

        $column = 'first_name';
        $expression = new ExpressionIndex(function ($item) use ($column) {
            if (!array_key_exists($column, $item)) {
                throw new \RuntimeException(sprintf('Undefined index: %s', $column));
            }

            return $item[$column];
        });

        $faker = Factory::create();
        $search_value = $faker->firstName;

        $output->writeln([
            '',
            sprintf(
                'Start tests with data size [%d] and searches repetition [%d]...',
                count($array),
                $searches
            ),
        ]);

        $phindexer_job = new PhindexerJob($array);
        $classic_job   = new ClassicJob($array);

        $this
            ->runJob($output, $phindexer_job, $expression, $search_value, $searches)
            ->runJob($output, $classic_job, $expression, $search_value, $searches)
        ;
    }

    /**
     * runJob
     *
     * @param OutputInterface $output
     * @param JobInterface    $job
     * @param ExpressionIndex $expression_index
     * @param string          $search_value
     * @param int             $repetition
     * @return PerformanceCheckForArray
     */
    protected function runJob(
        OutputInterface $output,
        JobInterface $job,
        ExpressionIndex $expression_index,
        string $search_value,
        int $repetition
    ): self {
        $profiler_job = new ProfilerJob(new RepetitiveJob($job, $repetition));

        $profiler_job->run($expression_index, $search_value);
        $output->writeln([
            '',
            'Results',
            '-------',
            sprintf('Strategy   : %s', get_class($job)),
            sprintf('Memory used: %f MB', $profiler_job->getConsumedMemory() / 1048576),
            sprintf('Time       : %f seconds', $profiler_job->getDuration()),
        ]);

        return $this;
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
