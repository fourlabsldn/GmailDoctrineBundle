<?php

namespace FL\GmailDoctrineBundle\Form\Type;

use FL\GmailDoctrineBundle\Entity\SyncSetting;
use Doctrine\ORM\EntityRepository;
use FL\GmailBundle\Services\Directory;
use FL\GmailBundle\Services\OAuth;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FromType
 * This class needs a list of userIds, and it uses OAuth and Directory to
 * construct a ChoiceType with email addresses.
 * @package FL\GmailDoctrineBundle
 */
class FromType extends AbstractType
{

    /**
     * @var string[]
     */
    private $emailChoices;

    /**
     * InboxType constructor.
     * @param OAuth $oAuth
     * @param Directory $directory
     * @param EntityRepository $syncSettingRepository
     */
    public function __construct(OAuth $oAuth, Directory $directory, EntityRepository $syncSettingRepository)
    {
        $domain = $oAuth->resolveDomain();
        $emailChoices = [];
        $syncSetting = $syncSettingRepository->findOneBy(['domain'=>$domain]);

        if ($syncSetting instanceof SyncSetting) {
            foreach ($syncSetting->getUserIds() as $userId) {
                $emailsOfUserId = $directory->resolveEmailsFromUserId($userId, $domain, Directory::MODE_RESOLVE_PRIMARY_ONLY);
                if (isset($emailsOfUserId[0])) {
                    $email = $emailsOfUserId[0];
                    $emailChoices[$email] = $email;
                }
            }
        } else {
            throw new \RuntimeException("No " . SyncSetting::class . " persisted yet.");
        }

        $this->emailChoices = $emailChoices;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->emailChoices,
        ]);
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
