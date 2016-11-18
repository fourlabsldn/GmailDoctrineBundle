<?php

namespace FL\GmailDoctrineBundle\Command;

use FL\GmailDoctrineBundle\Services\SyncWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SyncCommand.
 */
class SyncCommand extends Command
{
    const COMMAND_NAME = 'fl:gmail_doctrine:sync';

    /**
     * @var SyncWrapper
     */
    private $syncWrapper;

    /**
     * SyncCommand constructor.
     *
     * @param SyncWrapper $syncWrapper
     */
    public function __construct(SyncWrapper $syncWrapper)
    {
        $this->syncWrapper = $syncWrapper;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Sync emails.')
            ->setHelp('An admin account of a Google Apps enabled domain, authorises this application. After which, this command syncs the email for all the users in said domain.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting...');
        try {
            $this->syncWrapper->sync(35); // remember, this is per user!
        } catch (\Google_Service_Exception $e) {
            if ($e->getErrors()[0]['reason'] === 'authError') {
                $output->writeln('Auth error. Did you make sure there\'s an authenticated Google Apps account for this application?');

                return;
            } else {
                throw $e;
            }
        }

        $output->writeln('Finished...');
    }
}
