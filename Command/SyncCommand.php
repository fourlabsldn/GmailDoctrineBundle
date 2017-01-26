<?php

namespace FL\GmailDoctrineBundle\Command;

use Buzz\Exception\InvalidArgumentException;
use FL\GmailDoctrineBundle\Services\SyncWrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('mode', 'm', InputOption::VALUE_REQUIRED, 'Which mode? gmail_ids, gmail_messages, or both?')
            ->addOption('limit_messages_per_user', 'l', InputOption::VALUE_OPTIONAL, 'Limit messages synced per user. Only used for modes: gmail_messages, both')
            ->setHelp('An admin account of a Google Apps enabled domain, authorises this application. After which, this command syncs the email for all the users in said domain.')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Google_Service_Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting...');
        $this->validateInput($input);

        try {
            switch ($input->getOption('mode')) {
                case 'gmail_ids':
                    $this->syncWrapper->sync(0, SyncWrapper::MODE_SYNC_GMAIL_IDS); // messages per user limit doesn't matter here, so it's 0
                    break;
                case 'gmail_messages':
                    $this->syncWrapper->sync($input->getOption('limit_messages_per_user'), SyncWrapper::MODE_SYNC_GMAIL_MESSAGES); // remember, this is per user!
                    break;
                case 'both':
                    $this->syncWrapper->sync($input->getOption('limit_messages_per_user'), SyncWrapper::MODE_SYNC_ALL);
                    break;
            }
        } catch (\Google_Service_Exception $e) {
            if ($e->getErrors()[0]['reason'] === 'authError') {
                throw new InvalidArgumentException('Auth error. Did you make sure there\'s an authenticated Google Apps private key file for this application?');
            } else {
                throw $e;
            }
        }

        $output->writeln('Finished...');
    }

    /**
     * @param InputInterface $input
     *
     * @throws \InvalidArgumentException|\LogicException
     */
    private function validateInput(InputInterface $input)
    {
        switch ($input->getOption('mode')) {
            case 'gmail_ids':
                break;
            case 'gmail_messages':
                if (
                    (!$input->hasOption('limit_messages_per_user')) ||
                    $input->getOption('limit_messages_per_user') === null
                ) {
                    throw new \LogicException('Option "mode" = "gmail_messages" requires the option "limit_messages_per_user"');
                }
                $input->setOption('limit_messages_per_user', (int) $input->getOption('limit_messages_per_user'));
                break;
            case 'both':
                if (
                    (!$input->hasOption('limit_messages_per_user')) ||
                    $input->getOption('limit_messages_per_user') === null
                ) {
                    throw new \LogicException('Option "mode" = "both" requires the option "limit_messages_per_user"');
                }
                $input->setOption('limit_messages_per_user', (int) $input->getOption('limit_messages_per_user'));
                break;
            default:
                throw new \InvalidArgumentException('The "mode" option must be set to "gmail_ids", "gmail_messages", "both"');
        }
    }
}
