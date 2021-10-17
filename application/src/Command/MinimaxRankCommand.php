<?php /** @noinspection UnknownInspectionInspection */

/** @noinspection PhpUnused */

namespace App\Command;

use App\Service\MinimaxRankHelper;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MinimaxRankCommand extends Command
{
    protected static $defaultName = 'vote:minimaxrank';

    private MinimaxRankHelper $helper;

    /**
     * MinmaxRankCommand constructor.
     *
     */
    public function __construct(
        MinimaxRankHelper $helper
    ) {
        parent::__construct();
        $this->helper = $helper;
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    protected function configure()
    {
        parent::configure();
        $this
            ->setDescription('Calculate minimax ranking.')
            ->addArgument(
                'ballot_file',
                InputArgument::REQUIRED,
                'Path to CSV file of ballots'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try
        {
            $fp = fopen($input->getArgument('ballot_file'), 'rb');
            $headerLine = fgetcsv($fp, 1000);
            $this->helper->setTitles($headerLine);

            while ($ballot = fgetcsv($fp, 1000)) {
                $this->helper->addBallot($ballot);
            }

            fclose($fp);

            $rankedResults = $this->helper->getRanks();

            foreach($rankedResults as $result) {
                $io->writeln($result);
            }
            return 0;
        } catch (Exception $e) {
            $io->error('An error occurred while ranking the shows.');
            $io->error($e->getMessage());
            return 1;
        }
    }
}
