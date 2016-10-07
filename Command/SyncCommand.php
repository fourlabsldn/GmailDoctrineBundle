<?php

namespace FL\GmailDoctrineBundle\Command;

use FL\GmailDoctrineBundle\Services\SyncWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SyncCommand
 * @package FL\GmailDoctrineBundle\Command
 */
class SyncCommand extends Command
{
    const COMMAND_NAME = 'app:sync-emails';

    /**
     * @var SyncWrapper
     */
    private $syncWrapper;

    /**
     * SyncCommand constructor.
     * @param SyncWrapper $syncWrapper
     */
    public function __construct(SyncWrapper $syncWrapper)
    {
        $this->syncWrapper = $syncWrapper;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Sync emails.')
            ->setHelp('An admin account of a Google Apps enabled domain, authorises this application. After which, this command syncs the email for all the users in said domain.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting...\n');
        try {
            $this->syncWrapper->sync();
        } catch (\Exception $e) {
            $output->writeln('Exception! Did you make sure there\'s an authenticated Google Apps account for this application? ');
            throw $e;
        }
        $output->writeln('Finished...\n');
    }
}
